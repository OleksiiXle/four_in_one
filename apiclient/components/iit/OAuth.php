<?php

//================================================================================

require_once('EUSignCP.php');

//================================================================================

class AuthorizationCodeResponse
{
	public $access_token = null;
	public $token_type = null;
	public $expires_in = null;
	public $refresh_token = null;
	public $user_id = null;

	public function __construct(
		$response)
	{
		$this->access_token = $response['access_token'];
		$this->token_type = $response['token_type'];
		$this->expires_in = $response['expires_in'];
		$this->refresh_token = $response['refresh_token'];
		$this->user_id = $response['user_id'];
	}
}

//================================================================================

class EnvelopedUserInfoResponse
{
	public $encryptedUserInfo = null;
	public $error = null;
	public $message = null;

	public function __construct(
		$response)
	{
		$this->encryptedUserInfo = $response['encryptedUserInfo'];
		$this->error = $response['error'];
		$this->message = $response['message'];
	}
}

//================================================================================

class UserInfoResponse
{
	public $issuer = null;
	public $issuercn = null;
	public $serial = null;
	public $subject = null;
	public $subjectcn = null;
	public $locality = null;
	public $state = null;
	public $o = null;
	public $ou = null;
	public $title = null;
	public $lastname = null;
	public $middlename = null;
	public $givenname = null;
	public $email = null;
	public $address = null;
	public $phone = null;
	public $dns = null;
	public $edrpoucode = null;
	public $drfocode = null;

	public function __construct(
		$response)
	{
		$this->issuer = $response['issuer'];
		$this->issuercn = $response['issuercn'];
		$this->serial = $response['serial'];
		$this->subject = $response['subject'];
		$this->subjectcn = $response['subjectcn'];
		$this->locality = $response['locality'];
		$this->state = $response['state'];
		$this->o = $response['o'];
		$this->ou = $response['ou'];
		$this->title = $response['title'];
		$this->lastname = $response['lastname'];
		$this->middlename = $response['middlename'];
		$this->givenname = $response['givenname'];
		$this->email = $response['email'];
		$this->address = $response['address'];
		$this->phone = $response['phone'];
		$this->dns = $response['dns'];
		$this->edrpoucode = $response['edrpoucode'];
		$this->drfocode = $response['drfocode'];
	}
}

//================================================================================

class OAuth 
{

//================================================================================

	const REDIRECT_URI							= "https://ndl.univd.edu.ua/dstest/apiclient/site/auth";
	const ID_SERVER_URI							= "https://id.gov.ua/";
	const CLIENT_ID								= "";
	const CLIENT_SECRET							= "";

	/*
		Для шифрованого обміну з системою id.gov.ua необхідно вказати шлях до 
		ос. ключа в файлі, пароль та шлях до файла з сертифікатом ос. ключа 
		призначеного для шифрування
	*/
	const PK_FILE_PATH							= "/etc/pki/iit";
	const PK_PASSWORD							= "";
	const PK_ENV_CERT_FILE_PATH					= "";

//================================================================================

	private $useSSL = true;
	private $useProxy = false;
	private $proxyAddress = null;
	private $proxyPort = null;
	private $proxyLoginPassword = null;

//================================================================================

	private function makeRequest(
		$url)
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

//================================================================================

	public function getAuthURI()
	{
		return OAuth::ID_SERVER_URI.
			"?response_type=code".
			"&state=xyz".
			"&scope=".
			"&client_id=".OAuth::CLIENT_ID.
			"&redirect_uri=".OAuth::REDIRECT_URI;
	}

//--------------------------------------------------------------------------------

	public function getAuthorizationCode(
		$code)
	{
		$uri = OAuth::ID_SERVER_URI."get-access-token".
			"?grant_type=authorization_code".
			"&client_id=".OAuth::CLIENT_ID.
			"&client_secret=".OAuth::CLIENT_SECRET.
			"&code=".$code;
		$response = $this->makeRequest($uri);
		
		return new AuthorizationCodeResponse($response);
	}

//--------------------------------------------------------------------------------

	public function getUserInfo(
		$userId,
		$accessToken)
	{
		$euSign = !empty(OAuth::PK_FILE_PATH) ? 
			new EUSignCP() : null;
		if ($euSign)
		{
			$errorCode = $euSign->initialize(
				OAuth::PK_FILE_PATH,
				OAuth::PK_PASSWORD,
				OAuth::PK_ENV_CERT_FILE_PATH
			);
			
			if ($errorCode != EUSignCP::EU_ERROR_NONE)
			{
				throw new Exception("Crypto error: ". 
					$euSign->getErrorDescription(errorCode)
				);
			}
		}

		$uri = OAuth::ID_SERVER_URI."get-user-info".
			"?fields=issuer,issuercn,serial,subject,subjectcn,".
				"locality,state,o,ou,title,lastname,middlename,".
				"givenname,email,address,phone,dns,edrpoucode,drfocode".
			"&user_id=".$userId.
			"&access_token=".$accessToken;
		if ($euSign)
		{
			$uri = $uri.'&cert='.urlencode(
				urlencode($euSign->getEnvelopCert()));
		}
		
		$response = $this->makeRequest($uri);
		if ($euSign)
		{
			$senderInfo = null;
			$envResponse = new EnvelopedUserInfoResponse(
				$response
			);
			
			if (empty($envResponse->encryptedUserInfo))
			{
				throw new Exception("Get user info failed: ".
					$envResponse->message.
					'('.$envResponse->error.')');
			}
			
			$errorCode = $euSign->develop(
				base64_decode($envResponse->encryptedUserInfo),
				$data,
				$senderInfo
			);
			if ($errorCode != EUSignCP::EU_ERROR_NONE)
			{
				throw new Exception("Crypto error: ". 
					$euSign->getErrorDescription($errorCode)
				);
			}

			$response = json_decode($data, true);
		}

		return new UserInfoResponse($response);
	}

//================================================================================

}

//================================================================================

?>