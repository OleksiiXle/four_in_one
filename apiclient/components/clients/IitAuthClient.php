<?php

namespace app\components\clients;

use app\components\iit\modules\EnvelopedUserInfoResponse;
use app\components\iit\modules\EUSignCP;
use app\components\iit\modules\OAuth;
use Yii;
use yii\authclient\OAuth2;
use yii\authclient\OAuthToken;
use yii\httpclient\Client;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use app\models\User;
use app\models\UserToken;
use common\helpers\Functions;
use TheSeer\Tokenizer\Exception;

class IitAuthClient extends OAuth2
{
    /**
     * Initializes authenticated user attributes.
     * @return array auth user attributes.
     */
    protected function initUserAttributes()
    {

    }

    public $id_server_uri;
    public $client_id;
    public $client_secret;
    public $pk_password;
    public $pk_file_path;
    public $pk_env_sert_file_path;
    public $fields;
    public $auth_type;
    public $useCert = false;

    public $codeResponse = [];

    public $errorMessage = ['Unknowwn error'];
    public $successMessage = [];
    public $result = false;

    private $useSSL = true;
    private $useProxy = false;
    private $proxyAddress = null;
    private $proxyPort = null;
    private $proxyLoginPassword = null;


    public function init()
    {
        $params = Yii::$app->params;
        $this->id_server_uri = $params['iit']['id_server_uri'];
        $this->client_id = $params['iit']['client_id'];
        $this->client_secret = $params['iit']['client_secret'];
        $this->pk_password = $params['iit']['pk_password'];
        $this->pk_file_path = $params['iit']['pk_file_path'];
        $this->pk_env_sert_file_path = $params['iit']['pk_env_sert_file_path'];
        $this->fields = $params['iit']['fields'];
        $this->auth_type = $params['iit']['auth_type'];
        $this->useCert = $params['iit']['useCert']; //"/etc/pki/iit/";
    }

    /**
     * Composes user authorization URL.
     * @param array $params additional auth GET params.
     * @return string authorization URL.
     */
    public function buildAuthUrl(array $params = [])
    {
        $authState = $this->generateAuthState();
        $this->setState('authState', $authState);
        $defaultParams = [
            'response_type' => 'code',
            'state' => $authState,
            'client_id' => $this->client_id,
            'redirect_uri' => $this->getReturnUrl(),
            //   'auth_type' => $this->auth_type,
        ];

        return $this->composeUrl($this->authUrl, array_merge($defaultParams, $params));
    }

    /**
     * @return string return URL.
     */
    public function getReturnUrl()
    {
        $params[0] = Yii::$app->controller->getRoute();

        return Yii::$app->getUrlManager()->createAbsoluteUrl($params);
    }

    public function govnoCodeDispatcher()
    {
        if (isset($_GET['error'])) {
            return $this->doErrorHandler();
        } elseif (isset($_GET['code'])) {
            if ($this->doGetAccessTocken($_GET['code'])) {
                return $this->doGetUserInfo();
            }
        } else {
            return $this->doRedirect();
        }

        return false;
    }

    private function doRedirect()
    {
        Functions::log("CLIENT --- buildAuthUrl : ");
        $url = $this->buildAuthUrl();
        Functions::log("CLIENT --- authUrl : $url");
        Functions::log("CLIENT --- rediretc to authUrl...");
        Functions::log("CLIENT --- ***********************************************************************");

        return Yii::$app->getResponse()->redirect($url);
    }

    private function doGetAccessTocken($code)
    {
        $uri = $this->id_server_uri ."get-access-token"
            . "?grant_type=authorization_code"
            . "&client_id=" . $this->client_id
            . "&client_secret=" . $this->client_secret
            . "&code=" . $code;
        $this->codeResponse = $this->makeRequest($uri);
        if (
            empty($this->codeResponse) || !is_array($this->codeResponse) ||
            !isset($this->codeResponse['access_token']) || !isset($this->codeResponse['token_type']) ||
            !isset($this->codeResponse['expires_in']) || !isset($this->codeResponse['refresh_token']) ||
            !isset($this->codeResponse['user_id'])
        ) {
            //-- not ok
            $this->errorMessage = [
                'Wrong access token',
                $this->codeResponse
                ];
            $this->result = false;
            return false;
        } else {
            //--ok
            return true;
        }
    }

    private function doGetUserInfo()
    {
        Functions::log("CLIENT --- getUserInfo");
        Functions::log("CLIENT --- userId = " . $this->codeResponse['user_id']);
        Functions::log("CLIENT --- accessToken = "  . $this->codeResponse['access_token']);
        $uri = $this->id_server_uri . "get-user-info"
            . "?fields=" . $this->fields
            . "&user_id=" . $this->codeResponse['user_id']
            . "&access_token=" .$this->codeResponse['access_token'];

        if ($this->useCert) {
            $euSign = new EUSignCP();
            if ($euSign) {
                $errorCode = $euSign->initialize($this->pk_file_path, $this->pk_password, $this->pk_env_sert_file_path);
                if ($errorCode != EUSignCP::EU_ERROR_NONE) {
                    Functions::log("Crypto error: " . $euSign->getErrorDescription($errorCode));
                    $this->errorMessage = [
                        "Crypto error: " . $euSign->getErrorDescription($errorCode)
                    ];
                    $this->result = false;
                    return false;
                }
            }
            if ($euSign) {
                $uri = $uri.'&cert='.urlencode(urlencode($euSign->getEnvelopCert()));
            }
        }

        Functions::log('CLIENT --- $response = $this->makeRequest($uri)');
        Functions::log($uri);
        $response = $this->makeRequest($uri);
        Functions::log("CLIENT --- response:  ");
        Functions::logRequest();
        Functions::log($response);

        if ($this->useCert) {
            $senderInfo = null;
            $envResponse = new EnvelopedUserInfoResponse($response);
            if (empty($envResponse->encryptedUserInfo)) {
                $msg = "Get user info failed: ". $envResponse->message. '('.$envResponse->error.')';
                Functions::log($msg);
                $this->errorMessage = [
                    $msg
                ];
                $this->result = false;
                return false;
            }
            $errorCode = $euSign->develop(base64_decode($envResponse->encryptedUserInfo), $data, $senderInfo);
            if ($errorCode != EUSignCP::EU_ERROR_NONE) {
                Functions::log("Crypto error: " . $euSign->getErrorDescription($errorCode));
                $this->errorMessage = [
                    "Crypto error: " . $euSign->getErrorDescription($errorCode)
                ];
                $this->result = false;
                return false;
            }
            $response = json_decode($data, true);
        }

        $this->successMessage = $response;
        $this->result = true;

        return true;
    }

    private function doErrorHandler()
    {
        $this->errorMessage = array_merge($_REQUEST);
        $this->result = false;

        return false;
    }

    private function makeRequest($url)
    {
        $headers = array(
            'Content-Type: application/json'
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, "iit.oauth-client");

        if ($this->useSSL)
        {
            curl_setopt($ch, CURLOPT_SSLVERSION, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }

        if ($this->useProxy)
        {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxyAddress);
            curl_setopt($ch, CURLOPT_PROXYPORT, $this->proxyPort);
            curl_setopt($ch, CURLOPT_PROXYTYPE,
                $this->useSSL ? 'HTTPS' : 'HTTP');
            curl_setopt($ch, CURLOPT_PROXYUSERPWD,
                $this->proxyLoginPassword);
        }

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if ($response === false)
        {
            $error = curl_error($ch);
            curl_close($ch);

            throw new Exception($error);
        }

        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $response = substr($response, $headerSize);
        curl_close($ch);

        $response = json_decode($response, true);

        return $response;
    }

    /**
     * @return array list of user attributes
     */
    public function getUserAttributes()
    {
        return $this->successMessage;
    }


    protected function authOAuth2Iit___($client)
    {
        Functions::log("CLIENT --- authOAuth2Iit:", true);
        Functions::logRequest();
        if (isset($_GET['error'])) {
            if ($_GET['error'] == 'access_denied') {
                Functions::log('CLIENT --- *** access_denied');
                return $this->redirectCancel();
            } else {
                if (isset($_GET['error_description'])) {
                    $errorMessage = $_GET['error_description'];
                } elseif (isset($_GET['error_message'])) {
                    $errorMessage = $_GET['error_message'];
                } else {
                    $errorMessage = http_build_query($_GET);
                }
                Functions::log('CLIENT --- *** errorMessage:');
                Functions::log($errorMessage);
                throw new Exception('Auth error: ' . $errorMessage);
            }
        }

        // Get the access_token and save them to the session.
        if (isset($_GET['code'])) {
            if ($client->debug) {
                //  throw new Exception('has code = ' . $_GET['code']);
            }

            $code = (isset($_GET['code'])) ? $_GET['code'] : 'debug';
            Functions::log("CLIENT --- !!!!!! пришел code=$code");
            Functions::log("CLIENT --- пытаемся извлечь AccessToken... ");

            if ($client->iitGovnoCode($code)) {
                Functions::log("CLIENT --- если получилось извлечь токен - выполняем свой метод onAuthSuccess ...");
                return $this->authSuccess($client);
            } else {
                return $this->authCancel($client);
            }
        } else {
            Functions::log("CLIENT --- buildAuthUrl : ");
            $url = $client->buildAuthUrl();
            Functions::log("CLIENT --- authUrl : $url");
            Functions::log("CLIENT --- rediretc to authUrl...");
            Functions::log("CLIENT --- ***********************************************************************");
            /*
             https://id.gov.ua/
            ?response_type=code
            &client_id=3d0430da5e80f50cd7dad45f8e7adf2c
            &auth_type=dig_sign
            &state=fc4228705f387da5992abb890a75c4dd2657498a6565f5588fce40e1722c6b59
            &redirect_uri=http%3A%2F%2F192.168.33.11%2Fdstest%2Fapiclient%2Fsite%2Fauth
             */
            if ($client->debug) {
                //  throw new Exception('redirect to ' . $url);
            }
            return Yii::$app->getResponse()->redirect($url);
        }
    }

    /**
     * @param $code
     * @throws \yii\base\Exception
     */
    public function getUserInfo__($code)
    {
        Functions::log("CLIENT --- iitGovnoCode ");
        Functions::log("CLIENT --- code = $code ");

        $oAuth = new OAuth(['redirect_uri' => $this->getReturnUrl()]);
        $authCode = $oAuth->getAuthorizationCode($code);
        Functions::log("CLIENT --- authCode:");
        Functions::log($authCode);

        $userInfo = $oAuth->getUserInfo($authCode->user_id, $authCode->access_token);
        Functions::log("CLIENT --- userInfo: ");
        Functions::log($userInfo);

        return true;
  /*
        echo 'Інформація про користувача:<br>';
        echo 'SubjCN:'.$userInfo->subjectcn.'<br>';
        echo 'EDRPOU:'.$userInfo->edrpoucode.'<br>';
        echo 'DRFO:'.$userInfo->drfocode.'<br>';
        echo 'IssuerCN:'.$userInfo->issuercn.'<br>';
        echo 'Serial:'.$userInfo->serial.'<br>';
  */

    }

    public function fetchAccessToken__($authCode, array $params = [], User $user=null)
    {
        /*
         Должно прийти
        GET http(s)://url/redirect
        ?code=code (Код авторизації)
        &state=state (Значення, що надсилалось у запиті на кроці 3)
         */
        Functions::log("CLIENT --- class DsAuthClient extends OAuth2 public function fetchAccessToken(authCode, array params = [], User user=null) ");
        //-- проверяем state
        $authState = $this->getState('authState');
        Functions::log("CLIENT --- проверяем authState = $authState");
        if (!isset($_REQUEST['state']) || empty($authState) || strcmp($_REQUEST['state'], $authState) !== 0) {
            Functions::log("CLIENT --- authState не ОК");
            $this->errorMessage = 'Invalid auth state parameter.';
            return false;
        } else {
            Functions::log("CLIENT --- authState ОК, удаляем старый из сессии");
            $this->removeState('authState');
        }

        //-- подготавливаем запрос на завершення ідентифікації користувача
        /*
         GET / POST https://id.gov.ua/get-access-token
        ?grant_type=authorization_code
        &client_id= client_id
        &client_secret= client_secret
        &code=code (todo ?????? Примітка. Код авторизації (code) може бути використаний лише один раз. todo ??????)
        */

        $defaultParams = [
            'grant_type' => 'authorization_code',
            //      'client_id' => $this->clientId,
            //      'client_secret' => $this->clientSecret,
            'code' => $authCode,
            //   'redirect_uri' => $this->getReturnUrl(),
        ];
        Functions::log("CLIENT --- подготовка запроса на получение токена");
        Functions::log("CLIENT --- первичные параметры запроса:");
        Functions::log($defaultParams);

        $request = $this->createRequest()
            ->setMethod('POST')
            //  ->setFormat(Client::FORMAT_JSON)
            ->setUrl($this->tokenUrl)
            ->setData($defaultParams);

        $this->applyClientCredentialsToRequest($request);
        /*
         POST https://id.gov.ua/
            Content-Type: application/x-www-form-urlencoded; charset=UTF-8
            grant_type=authorization_code
            &code=debug
            &client_id=3d0430da5e80f50cd7dad45f8e7adf2c
            &client_secret=82ec2ce6bc71bf78cbca7228021f7ac4840a80e1
         */
        Functions::log("CLIENT --- добавляем в запрос ClientCredentials");
        Functions::log("CLIENT --- остаточные данные запроса:");
        Functions::log($request->getFullUrl());
        Functions::log($request->getData());
        //   Functions::log((string)$request);
        Functions::log("CLIENT --- посылаем запрос на получение токена...");

        $response = $this->sendRequest($request);
        //-- должно прийти:
        /*
         Content-Type: application/json
            {
                «access_token»:«»,
                «token_type»:«bearer»,
                «expires_in»:«»,
                «refresh_token»:«»,
                «user_id»:«»
            }
         */
        Functions::log("CLIENT --- обрабатываем ответ ...");
        Functions::log("CLIENT --- пришло:");
        Functions::log($response);
        //return false;

        if (!$this->requestIsOk) {
            $this->removeState('authState');
            $this->errorMessage = $this->requestSendMessage;
            return false;
        }
        Functions::log("CLIENT --- пытаемся извлечь токен из того что пришло");
        $token = $this->createToken(['params' => $response]);

        Functions::log("CLIENT --- сохраняем токен в сессию");
        $this->setAccessToken($token);

        $user_id = $response['user_id'];
        $token = $response['access_token'];
        $ret = $this->getUserProfile($token, $user_id);

        Functions::log("CLIENT --- обработка токена закончена");

        return $ret;
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
     * Gets new auth token to replace expired one.
     * @param OAuthToken $token expired auth token.
     * @return OAuthToken new auth token.
     */
    public function refreshAccessToken(OAuthToken $token)
    {

    }

    /**
     * Applies access token to the HTTP request instance.
     * @param \yii\httpclient\Request $request HTTP request instance.
     * @param OAuthToken $accessToken access token instance.
     * @since 2.1
     */
    public function applyAccessTokenToRequest($request, $accessToken)
    {

    }
}