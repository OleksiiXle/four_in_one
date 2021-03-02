<?php

namespace app\components\iit\modules;

use common\helpers\Functions;
use yii\base\BaseObject;
use yii\base\Exception;

class OAuth extends BaseObject
{

//================================================================================

   // const redirect_uri							= "https://ndl.univd.edu.ua/dstest/apiclient/site/auth";
    /*
        Для шифрованого обміну з системою id.gov.ua необхідно вказати шлях до 
        ос. ключа в файлі, пароль та шлях до файла з сертифікатом ос. ключа 
        призначеного для шифрування
    */

    public $redirect_uri = '';
    public $id_server_uri;
    public $client_id;
    public $client_secret;
    public $pk_file_path;
    public $pk_password;
    public $pk_env_sert_file_path;

    private $useSSL = true;
    private $useProxy = false;
    private $proxyAddress = null;
    private $proxyPort = null;
    private $proxyLoginPassword = null;

    public function init()
    {
        parent::init();
        $params = \Yii::$app->params;
        $this->id_server_uri         = $params['iit']['id_server_uri']; //"https://id.gov.ua/";
        $this->client_id             = $params['iit']['client_id'];
        $this->client_secret         = $params['iit']['client_secret'];
        $this->pk_password           = $params['iit']['pk_password'];
        $this->pk_file_path          = $params['iit']['pk_file_path'];
        $this->pk_env_sert_file_path = $params['iit']['pk_env_sert_file_path']; //"/etc/pki/iit/";

    }

    /**
     * @param $url
     * @return bool|mixed|string
     * @throws Exception
     */
    private function makeRequest($url)
    {
        $headers = array('Content-Type: application/json');

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
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
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
        if (empty($response))
        {
            throw new Exception('Empty response');
        }

        return $response;
    }

    /**
     * @return string
     */
    public function getAuthURI()
    {
        return $this->id_server_uri .
            "?response_type=code" .
            "&state=xyz" .
            "&scope=" .
            "&client_id=" . $this->client_id.
            "&redirect_uri=" . $this->redirect_uri;
    }

    /**
     * @param $code
     * @return AuthorizationCodeResponse
     * @throws Exception
     */
    public function getAuthorizationCode($code)
    {
        $uri = $this->id_server_uri."get-access-token".
            "?grant_type=authorization_code".
            "&client_id=".$this->client_id.
            "&client_secret=".$this->client_secret.
            "&code=".$code;
        Functions::log("CLIENT --- getAuthorizationCode send request");
        Functions::log("CLIENT --- uri = $uri ");

        $response = $this->makeRequest($uri);

        return new AuthorizationCodeResponse($response);
    }

    /**
     * @param $userId
     * @param $accessToken
     * @return UserInfoResponse
     * @throws Exception
     */
    public function getUserInfo($userId, $accessToken)
    {
        Functions::log("CLIENT --- getUserInfo");
        Functions::log("CLIENT --- userId = $userId ");
        Functions::log("CLIENT --- accessToken = $accessToken ");

        $euSign = !empty($this->pk_file_path) ? new EUSignCP() : null;

        if ($euSign) {
            $errorCode = $euSign->initialize($this->pk_file_path, $this->pk_password, $this->pk_env_sert_file_path);
            if ($errorCode != EUSignCP::EU_ERROR_NONE) {
                throw new Exception("Crypto error: " . $euSign->getErrorDescription($errorCode));
            }
        }
        $uri = $this->id_server_uri."get-user-info".
            "?fields=issuer,issuercn,serial,subject,subjectcn,".
            "locality,state,o,ou,title,lastname,middlename,".
            "givenname,email,address,phone,dns,edrpoucode,drfocode".
            "&user_id=".$userId.
            "&access_token=".$accessToken;
        if ($euSign) {
            $uri = $uri.'&cert='.urlencode(urlencode($euSign->getEnvelopCert()));
        }
        $response = $this->makeRequest($uri);
        Functions::log('CLIENT --- $response = $this->makeRequest($uri)');
        Functions::log("CLIENT --- response:  ");
        Functions::logRequest();
        Functions::log($response);


        if ($euSign) {
            $senderInfo = null;
            $envResponse = new EnvelopedUserInfoResponse($response);
            if (empty($envResponse->encryptedUserInfo)) {
                throw new Exception("Get user info failed: ". $envResponse->message. '('.$envResponse->error.')');
            }
            $errorCode = $euSign->develop(base64_decode($envResponse->encryptedUserInfo), $data, $senderInfo);
            if ($errorCode != EUSignCP::EU_ERROR_NONE) {
                throw new Exception("Crypto error: ". $euSign->getErrorDescription($errorCode));
            }
            $response = json_decode($data, true);
        }

        return new UserInfoResponse($response);
    }

//================================================================================

}