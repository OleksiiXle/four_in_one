<?php

namespace app\components\clients;

use Yii;
use yii\authclient\OAuth2;
use yii\authclient\OAuthToken;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use app\models\User;
use app\models\UserToken;
use common\helpers\Functions;
use TheSeer\Tokenizer\Exception;

class XapiAuthClient extends OAuth2
{
    /*
             if (!$response->getIsOk()) {
            throw new InvalidResponseException($response, 'Request failed with code: ' . $response->getStatusCode() . ', message: ' . $response->getContent());
        }

     */
    public $signupUrl;
    public $errorMessage = '';
    public $provider_id = 'xapi';
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
        $response = $request->send();

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

    public function fetchAccessToken($authCode, array $params = [], User $user=null)
    {
        if ($user === null) {
            $user_id = Yii::$app->user->getId();
            $user = User::findOne($user_id);
        }
        Functions::log("CLIENT --- class XapiAuthClient extends OAuth2 public function fetchAccessToken(authCode, array params = [], User user=null) ");
        if ($this->validateAuthState) {
            $authState = $this->getState('authState');
            Functions::log("CLIENT --- проверяем authState = $authState");
            if (!isset($_REQUEST['state']) || empty($authState) || strcmp($_REQUEST['state'], $authState) !== 0) {
                Functions::log("CLIENT --- authState не ОК");
                $this->errorMessage = 'Invalid auth state parameter.';
                return false;
                // throw new HttpException(400, 'Invalid auth state parameter.');
            } else {
                Functions::log("CLIENT --- authState ОК, удаляем старый из сессии");
                $this->removeState('authState');
            }
        }

        $defaultParams = [
            'code' => $authCode,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getReturnUrl(),
        ];
        Functions::log("CLIENT --- подготовка запроса на получение токена");
        Functions::log("CLIENT --- параметры запроса:");
        Functions::log(array_merge($defaultParams, $params));

        $request = $this->createRequest()
            ->setMethod('POST')
            ->setUrl($this->tokenUrl)
            ->setData(array_merge($defaultParams, $params));

        $this->applyClientCredentialsToRequest($request);
        Functions::log("CLIENT --- добавляем в запрос ClientCredentials");
        Functions::log("CLIENT --- данные запроса:");
        Functions::log($request->getFullUrl());
        Functions::log($request->getData());
        //   Functions::log((string)$request);
        Functions::log("CLIENT --- посылаем запрос на получение токена...");

        $response = $this->sendRequest($request);
        Functions::log("CLIENT --- обрабатываем ответ ...");
        Functions::log("CLIENT --- пришло:");
        Functions::log($response);

        if (!$this->requestIsOk) {
            if ($this->validateAuthState) {
                $this->removeState('authState');
            }
            $this->errorMessage = $this->requestSendMessage;
            return false;
        }
        Functions::log("CLIENT --- пытаемся извлечь токен из того что пришло");
        $token = $this->createToken(['params' => $response]);

        Functions::log("CLIENT --- сохраняем токен в сессию");
        $this->setAccessToken($token);

        Functions::log("CLIENT --- сохраняем токен в БД");
        $ret = $this->storeTokenToDb($token, $user);
        if (!$ret){
            $user->addError('username', $this->errorMessage);
            Functions::log("CLIENT --- ошибка сохранения токена в БД");
            Functions::log($this->errorMessage);
            return false;
        }
        Functions::log("CLIENT --- обработка токена закончена");

        return true;
        /*
                $r=1;
                $userProfile = $this->api('/user/userinfo', 'POST', ['id' => $user->id] );
                $tokenParams = [
                  'tokenParamKey' =>  $token->tokenParamKey,
                  'tokenSecretParamKey' =>  $token->tokenSecretParamKey,
                  'created_at' =>  $token->createTimestamp,
                  'expireDurationParamKey' =>  $token->expireDurationParamKey,
                  'access_token' =>  $token->getParam('access_token'),
                  'expires_in' =>  $token->getParam('expires_in'),
                  'token_type' =>  $token->getParam('token_type'),
                  'scope' =>  $token->getParam('scope'),
                  'refresh_token' =>  $token->getParam('refresh_token'),
                    ];
        */
        /*
        $r = $this->removeState('token');
        $token_ = $this->getState('token');
        $this->setAccessToken($token);
        $token_ = $this->getState('token');
        */
    }

    /**
     * Restores access token.
     * @return OAuthToken auth token.
     */
    protected function restoreAccessToken()
    {
        $token = $this->getState('token');
        if (!is_object($token)){
            $token = $this->RestoreTokenFromDb();
        }
        if (is_object($token)) {
            /* @var $token OAuthToken */
            if ($token->getIsExpired() && $this->autoRefreshAccessToken) {
                $token = $this->refreshAccessToken($token);
            }
        } else {
            return false;
          //  $token = $this->refreshAccessToken($token);
          //  throw new \Exception($this->errorMessage);
        }
        return $token;
    }

    /**
     * Gets new auth token to replace expired one.
     * @param OAuthToken $token expired auth token.
     * @return OAuthToken new auth token.
     */
    public function refreshAccessToken(OAuthToken $token)
    {
        $i=1;
        $params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token->getParam('refresh_token'),
            'scope' =>  $token->getParam('scope'),
         //   'redirect_uri' => $this->getReturnUrl(),
        ];
        $request = $this->createRequest()
            ->setMethod('POST')
            ->setUrl($this->tokenUrl)
            ->setData($params);

        $this->applyClientCredentialsToRequest($request);

        $response = $this->sendRequest($request);
        $token = $this->createToken(['params' => $response]);
        $this->setAccessToken($token);

        if (!$this->storeTokenToDb($token, false, true)){
            return false;
            throw new \Exception($this->errorMessage);
        }
        return $token;
    }

    /**
     * Запись в БД токена клиента
     * @param OAuthToken $token
     * @param $client
     * @param $profile - обновлять профиль или нет
     * @return bool
     */
    public function storeTokenToDb(OAuthToken $token, $user, $profile = true)
    {
      //  if (\Yii::$app->user->isGuest){
     //       return true;
     //   }
        $t=1;

        try{
            if (!$user ){
                $clientId = \Yii::$app->user->id;
                $user = User::findOne($clientId);
                if (!isset($user)){
                    $this->errorMessage = "Client $clientId not found";
                    return false;
                   // throw new NotFoundHttpException("Client $clientId not found");
                }
            }
            $headers = [];

         //   $headers = ['Authorization' => 'Bearer ' . $token->params['access_token']];

            $userProfile = ($profile) ? $this->api('/user/userinfo', 'POST', ['id' => $user->id], $headers ) : [];
            if (!$this->requestIsOk) {
                $this->errorMessage = $this->requestSendMessage;
                return false;
            }
            $tokenParams = [
                'tokenParamKey' =>  $token->tokenParamKey,
                'tokenSecretParamKey' =>  $token->tokenSecretParamKey,
                'created_at' =>  $token->createTimestamp,
                'expireDurationParamKey' =>  $token->expireDurationParamKey,
                'access_token' =>  $token->getParam('access_token'),
                'expires_in' =>  $token->getParam('expires_in'),
                'token_type' =>  $token->getParam('token_type'),
                'scope' =>  $token->getParam('scope'),
                'refresh_token' =>  $token->getParam('refresh_token'),
                'provider_id' =>  $this->provider_id,
            ];
            $ret = $user->refreshToken($this->getStateKeyPrefix() . '_token', $tokenParams, $userProfile );
            if (!$ret){
                $this->errorMessage = $user->getFirstError('id');
                return false;
            } else{
                return true;
            }

        } catch (\Exception $e){
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * Извлечение токена из БД
     * @return bool
     */
    public function RestoreTokenFromDb()
    {
        $token = false;//1571731601
        if (\Yii::$app->user->isGuest){
            return false;
        }
        try{
            $clientId = \Yii::$app->user->id;
            $provider = $this->getStateKeyPrefix() . '_token';
            $dbToken = UserToken::findOne(['client_id' => $clientId, 'provider' => $provider]);
            if (empty($dbToken)){
                $this->errorMessage = "Token '$this->clientId' not found in DB for user= $clientId";
                return false;

            }
            $token = $this->createToken(['params' => $dbToken->getAttributes()]);
            $token = $this->refreshAccessToken($token);
          //  $this->setAccessToken($token);
        } catch (\Exception $e){
            $this->errorMessage = $e->getMessage();
        }
        return $token;
    }

    /**
     * Удаление на АПИ всех токенов юсера по провайдеру
     */
    public function removeTokens($userApi_id)
    {
        $i=1;
        try{
            $params = [
                'grant_type' => 'logout',
                'user_id' => $userApi_id,
            ];
            $request = $this->createRequest()
                ->setMethod('POST')
                ->setUrl($this->tokenUrl)
                ->setData($params);

            $this->applyClientCredentialsToRequest($request);
            $response = $this->sendRequest($request);

            $this->removeState($this->getStateKeyPrefix() . '_token');

            $dbTokenDel = UserToken::deleteAll(['api_id' => $userApi_id, 'provider' => $this->fullClientId]);
            return true;
        } catch (\Exception $e){
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * Composes user authorization URL.
     * @param array $params additional auth GET params.
     * @return string authorization URL.
     */
    public function buildSignupUrl(array $params = [])
    {
        $defaultParams = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->getReturnUrl(),
            'xoauth_displayname' => Yii::$app->name,
        ];
        if (!empty($this->scope)) {
            $defaultParams['scope'] = $this->scope;
        }

        if ($this->validateAuthState) {
            $authState = $this->generateAuthState();
            $this->setState('authState', $authState);
            $defaultParams['state'] = $authState;
        }

        return $this->composeUrl($this->signupUrl, array_merge($defaultParams, $params));
    }

    protected function initUserAttributes()
    {
        return $this->api('user/userinfo', 'GET');
    }

    /**
     * Performs request to the OAuth API returning response data.
     * You may use [[createApiRequest()]] method instead, gaining more control over request execution.
     * @see createApiRequest()
     * @param string $apiSubUrl API sub URL, which will be append to [[apiBaseUrl]], or absolute API URL.
     * @param string $method request method.
     * @param array|string $data request data or content.
     * @param array $headers additional request headers.
     * @return array API response data.
     */
    public function api($apiSubUrl, $method = 'GET', $data = [], $headers = [])
    {
        Functions::log("CLIENT --- public function api(apiSubUrl, method = 'GET', data = [], headers = [])");
        Functions::log("CLIENT --- apiSubUrl = $apiSubUrl");
        Functions::log("CLIENT --- method = $method");
        Functions::log("CLIENT --- data:");
        Functions::log($data);

        $request = $this->createApiRequest()
            ->setMethod($method)
            ->setUrl($apiSubUrl)
            ->addHeaders($headers);

        if (!empty($data)) {
            if (is_array($data)) {
                $request->setData($data);
            } else {
                $request->setContent($data);
            }
        }
     //   Functions::log("CLIENT --- подготовленный запрос:");
      //  Functions::log((string)$request);
        Functions::log("CLIENT --- отправляем ...");
        $response = $this->sendRequest($request);
        Functions::log("CLIENT --- получили ответ:");
        Functions::log($response);

        return $response;
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