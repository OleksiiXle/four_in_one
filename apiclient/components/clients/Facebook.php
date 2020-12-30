<?php

namespace app\components\clients;

use Yii;
use common\helpers\Functions;
use yii\authclient\OAuth2;
use yii\authclient\OAuthToken;
use app\models\User;
use app\models\UserToken;

/**
 * Facebook allows authentication via Facebook OAuth.
 *
 * In order to use Facebook OAuth you must register your application at <https://developers.facebook.com/apps>.
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'facebook' => [
 *                 'class' => 'yii\authclient\clients\Facebook',
 *                 'clientId' => 'facebook_client_id',
 *                 'clientSecret' => 'facebook_client_secret',
 *             ],
 *         ],
 *     ]
 *     // ...
 * ]
 * ```
 *
 * @see https://developers.facebook.com/apps
 * @see http://developers.facebook.com/docs/reference/api
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Facebook extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $authUrl = 'https://www.facebook.com/dialog/oauth';
    /**
     * {@inheritdoc}
     */
    public $tokenUrl = 'https://graph.facebook.com/oauth/access_token';
    /**
     * {@inheritdoc}
     */
    public $apiBaseUrl = 'https://graph.facebook.com';
    /**
     * {@inheritdoc}
     */
    public $scope = 'email';
    /**
     * @var array list of attribute names, which should be requested from API to initialize user attributes.
     * @since 2.0.5
     */
    public $attributeNames = [
        'name',
        'email',
    ];
    /**
     * {@inheritdoc}
     */
    public $autoRefreshAccessToken = false; // Facebook does not provide access token refreshment
    /**
     * @var bool whether to automatically upgrade short-live (2 hours) access token to long-live (60 days) one, after fetching it.
     * @see exchangeToken()
     * @since 2.1.3
     */
    public $autoExchangeAccessToken = false;
    /**
     * @var string URL endpoint for the client auth code generation.
     * @see https://developers.facebook.com/docs/facebook-login/access-tokens/expiration-and-extension
     * @see fetchClientAuthCode()
     * @see fetchClientAccessToken()
     * @since 2.1.3
     */
    public $clientAuthCodeUrl = 'https://graph.facebook.com/oauth/client_code';

    public $errorMessage = 'Some errors';

    public $signupUrl;
    public $provider_id = 'facebook';
    public $requestIsOk = false;
    public $requestSendMessage = '';
    public $requestSendCode = 0;

    private $_fullClientId;

    /**
     * @var array authenticated user attributes.
     */
    private $_userAttributes;

    /**
     * Sends the given HTTP request, returning response data.
     * @param \yii\httpclient\Request $request HTTP request to be sent.
     * @return array response data.
     * @throws InvalidResponseException on invalid remote response.
     * @since 2.1
     */
    protected function sendRequest($request)
    {
        $this->errorMessage = '';
        Functions::log("CLIENT --- ******* protected function sendRequest(request)");
        $response = $request->send();
        Functions::log("CLIENT --- response:");
        Functions::log($response);

        $this->requestIsOk = $response->getIsOk();
        $this->requestSendCode = $response->getStatusCode();
        if (!$this->requestIsOk) {
            $responseData = $response->getData();
            $this->requestSendMessage = 'Request failed with code:' . $this->requestSendCode . ' - ' . $responseData['name']
                . '<br>' . str_replace(PHP_EOL, '<br>' , $responseData['message']);
            return [];
        }

        return $response->getData();
    }

    /**
     * @return mixed
     */
    public function getFullClientId()
    {
        $this->_fullClientId = $this->getStateKeyPrefix() . '_token';
        return $this->_fullClientId;
    }

    /**
     * {@inheritdoc}
     */
    protected function initUserAttributes()
    {
        return $this->api('me', 'GET', [
            'fields' => implode(',', $this->attributeNames),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAccessToken($authCode, array $params = [], $user = null)
    {
        if ($user === null) {
            $user_id = Yii::$app->user->getId();
            $user = User::findOne($user_id);
        }

        Functions::log('CLIENT --- Получаем AccessToken');
        Functions::log("CLIENT --- app\components\clients\\class Facebook extends OAuth2");
        Functions::log("CLIENT --- protected function public function fetchAccessToken(authCode, array params = []):");
        Functions::log("CLIENT --- authCode = $authCode");
        Functions::log("CLIENT --- params :");
        Functions::log($params);
        if ($this->validateAuthState) {
            Functions::log("CLIENT --- берем из сессии authState и сравниваем с пришедшим state");
            $authState = $this->getState('authState');
            Functions::log("CLIENT --- authState = $authState");
            if (!isset($_REQUEST['state']) || empty($authState) || strcmp($_REQUEST['state'], $authState) !== 0) {
                Functions::log("CLIENT --- authState не ОК");
                $this->errorMessage = 'Invalid auth state parameter.';
                return false;
                //throw new HttpException(400, 'Invalid auth state parameter.');
            } else {
                $this->removeState('authState');
            }
            Functions::log("CLIENT --- пришло = " . $_REQUEST['state']);
        }
        Functions::log("CLIENT --- если authCode совпал - готовим запрос на получение токена:");
        $defaultParams = [
            'code' => $authCode,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getReturnUrlForToken(),
            //  'redirect_uri' => $this->getReturnUrl(),
            //    'redirect_uri' => 'https://8a657ac5cc1f.ngrok.io/dstest/apiclient/site/auth?authclient=facebook',
        ];
        Functions::log("CLIENT --- подготовка запроса на получение токена");
        Functions::log("CLIENT --- параметры запроса:");
        Functions::log(array_merge($defaultParams, $params));

        Functions::log("CLIENT --- формируем запрос:");
        $request = $this->createRequest()
            ->setMethod('POST')
            ->setUrl($this->tokenUrl)
            ->setData(array_merge($defaultParams, $params));
        Functions::log((string)$request);

        Functions::log("CLIENT --- добавляем в запрос свои clientId и clientSecret");

        $this->applyClientCredentialsToRequest($request);
        Functions::log("CLIENT --- добавляем в запрос ClientCredentials");
        Functions::log("CLIENT --- данные запроса:");
        Functions::log($request->getFullUrl());
        Functions::log($request->getData());
        //   Functions::log((string)$request);
        Functions::log("CLIENT --- посылаем запрос на получение токена...");
        $response = $this->sendRequest($request);

        Functions::log("CLIENT --- Пришло:", true);
        Functions::logRequest();

        if (!$this->requestIsOk) {
            if ($this->validateAuthState) {
                $this->removeState('authState');
            }
            $this->errorMessage = $this->requestSendMessage;
            return false;
        }

        $token = $this->createToken(['params' => $response]);
        Functions::log("CLIENT --- получился токен - объект класса " . get_class($token) . " :");
        Functions::log($token);

        $this->setAccessToken($token);
        Functions::log("CLIENT --- записываем его в сессию setState('token', token);");
        //-----------
        // $token = parent::fetchAccessToken($authCode, $params);
        if ($this->autoExchangeAccessToken) {
            $token = $this->exchangeAccessToken($token);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAccessTokenToRequest($request, $accessToken)
    {
        parent::applyAccessTokenToRequest($request, $accessToken);

        $data = $request->getData();
        if (($machineId = $accessToken->getParam('machine_id')) !== null) {
            $data['machine_id'] = $machineId;
        }
        $data['appsecret_proof'] = hash_hmac('sha256', $accessToken->getToken(), $this->clientSecret);
        $request->setData($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultName()
    {
        return 'facebook';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return 'Facebook';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultViewOptions()
    {
        return [
            'popupWidth' => 860,
            'popupHeight' => 480,
        ];
    }

    /**
     * Creates token from its configuration.
     * @param array $tokenConfig token configuration.
     * @return OAuthToken token instance.
     */
    protected function createToken(array $tokenConfig = [])
    {
        Functions::log("CLIENT --- app\components\clients\\class Facebook extends OAuth2");
        Functions::log("CLIENT --- protected function createToken(array tokenConfig = []):");
        Functions::log("CLIENT --- tokenConfig:");
        Functions::log($tokenConfig);

        $tokenConfig['tokenParamKey'] = 'access_token';
        if (!array_key_exists('class', $tokenConfig)) {
            $tokenConfig['class'] = OAuthToken::className();
        }
        Functions::log("CLIENT --- создаем объект из tokenConfig");
        Functions::log("CLIENT --- tokenConfig:");
        Functions::log($tokenConfig);

        return Yii::createObject($tokenConfig);
    }

    /**
     * Exchanges short-live (2 hours) access token to long-live (60 days) one.
     * Note that this method will success for already long-live token, but will not actually prolong it any further.
     * Pay attention, that this method will fail on already expired access token.
     * @see https://developers.facebook.com/docs/facebook-login/access-tokens/expiration-and-extension
     * @param OAuthToken $token short-live access token.
     * @return OAuthToken long-live access token.
     * @since 2.1.3
     */
    public function exchangeAccessToken(OAuthToken $token)
    {
        $params = [
            'grant_type' => 'fb_exchange_token',
            'fb_exchange_token' => $token->getToken(),
        ];

        $request = $this->createRequest()
            ->setMethod('POST')
            ->setUrl($this->tokenUrl)
            ->setData($params);

        $this->applyClientCredentialsToRequest($request);

        $response = $this->sendRequest($request);

        $token = $this->createToken(['params' => $response]);
        $this->setAccessToken($token);

        return $token;
    }

    /**
     * Requests the authorization code for the client-specific access token.
     * This make sense for the distributed applications, which provides several Auth clients (web and mobile)
     * to avoid triggering Facebook's automated spam systems.
     * @see https://developers.facebook.com/docs/facebook-login/access-tokens/expiration-and-extension
     * @see fetchClientAccessToken()
     * @param OAuthToken|null $token access token, if not set [[accessToken]] will be used.
     * @param array $params additional request params.
     * @return string client auth code.
     * @since 2.1.3
     */
    public function fetchClientAuthCode(OAuthToken $token = null, $params = [])
    {
        if ($token === null) {
            $token = $this->getAccessToken();
        }

        $params = array_merge([
            'access_token' => $token->getToken(),
            'redirect_uri' => $this->getReturnUrl(),
        ], $params);

        $request = $this->createRequest()
            ->setMethod('POST')
            ->setUrl($this->clientAuthCodeUrl)
            ->setData($params);

        $this->applyClientCredentialsToRequest($request);

        $response = $this->sendRequest($request);

        return $response['code'];
    }

    /**
     * Fetches access token from client-specific authorization code.
     * This make sense for the distributed applications, which provides several Auth clients (web and mobile)
     * to avoid triggering Facebook's automated spam systems.
     * @see https://developers.facebook.com/docs/facebook-login/access-tokens/expiration-and-extension
     * @see fetchClientAuthCode()
     * @param string $authCode client auth code.
     * @param array $params
     * @return OAuthToken long-live client-specific access token.
     * @since 2.1.3
     */
    public function fetchClientAccessToken($authCode, array $params = [])
    {
        $params = array_merge([
            'code' => $authCode,
            'redirect_uri' => $this->getReturnUrl(),
            'client_id' => $this->clientId,
        ], $params);

        $request = $this->createRequest()
            ->setMethod('POST')
            ->setUrl($this->tokenUrl)
            ->setData($params);

        $response = $this->sendRequest($request);

        $token = $this->createToken(['params' => $response]);
        $this->setAccessToken($token);

        return $token;
    }

    /**
     * Composes user authorization URL.
     * @param array $params additional auth GET params.
     * @return string authorization URL.
     */
    public function buildAuthUrl(array $params = [])
    {
        $tmp = 1;

        $defaultParams = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->getReturnUrl(),
            'xoauth_displayname' => Yii::$app->name,
        ];
        Functions::log("CLIENT --- defaultParams for AuthUrl:");
        Functions::log($defaultParams);
        if (!empty($this->scope)) {
            Functions::log("CLIENT --- add scope to defaultParams : scope = $this->scope");
            $defaultParams['scope'] = $this->scope;
        }

        if ($this->validateAuthState) {
            $authState = $this->generateAuthState();
            Functions::log("CLIENT --- generateAuthState : AuthState = $authState");
            $this->setState('authState', $authState);
            Functions::log("CLIENT --- saving  AuthState in session (app\components\clients\Facebook_facebook_authState = $authState)");
            $defaultParams['state'] = $authState;
        }
        Functions::log("CLIENT --- Params for AuthUrl:");
        Functions::log($defaultParams);

        return $this->composeUrl($this->authUrl, array_merge($defaultParams, $params));
    }
    /**
     * @return string return URL.
     */
    public function getReturnUrl()
    {
        $returnUrl = str_replace('http', 'https', Yii::$app->getRequest()->getAbsoluteUrl());
        return $returnUrl;
    }

    /**
     * @return string return URL.
     */
    public function getReturnUrlForToken()
    {
        $returnUrl = Yii::$app->getRequest()->getHostInfo() . str_replace('web/', '',$_SERVER['REDIRECT_URL']) . '?authclient=facebook';
        $returnUrl = str_replace('http', 'https', $returnUrl);
        return $returnUrl;
    }

    /**
     * @return array list of user attributes
     */
    public function getUserAttributes()
    {
        if ($this->_userAttributes === null) {
            $this->_userAttributes = $this->normalizeUserAttributes($this->initUserAttributes());
        }

        return $this->_userAttributes;
    }
}