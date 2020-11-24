<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\di\Instance;
use yii\httpclient\Client;
use yii\httpclient\Request;
use app\models\User;
//use app\components\ModelSessionStorageBehavior;


/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    const USER_NAME_PASSWORD_PATTERN       = '/^[a-zA-Z0-9_]+$/ui'; //--маска для пароля

    public $username;
    public $password;
    public $provider;
    public $rememberMe = true;
    public $reCaptcha;
    public $errorContent = '';

    protected $_user = false;
/*
    public function behaviors()
    {
        return [
            'ModelSessionStorage' => [
                'class' => ModelSessionStorageBehavior::className(),
            ],
        ];
    }
*/

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            ['username',  'string', 'min' => 3, 'max' => 50],
            ['password',  'string', 'min' => 3, 'max' => 50],
            ['provider',  'string', 'min' => 3, 'max' => 50],
            [['username', 'password'], 'match', 'pattern' => self::USER_NAME_PASSWORD_PATTERN],

         //   ['password', 'match', 'pattern' =>  User::USER_PASSWORD_PATTERN, 'message' => User::USER_PASSWORD_ERROR_MESSAGE],

            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            //    [['reCaptcha'], \himiklab\yii2\recaptcha\ReCaptchaValidator::className(), 'secret' => $siteSettings->captcha_key_private],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    public function getIdentity() {
        return $this->_user;
    }

    public function attributeLabels() {
        return [
            'password' => 'Пароль',
            'rememberMe' => 'Запомнить',
        ];
    }

    public static function providers()
    {
        $t=Yii::$app->authClientCollection->clients;
        $ret = [
            'none' => 'none',
        ];
        foreach ($t as $client){
            $ret[$client->clientId] = $client->clientId;
        }
        return $ret;

    }


    public function clientLogin()
    {
        /*
                     if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

         */
        $tmp = 1;
        if ($this->validate()) {
            //--- проверить логин + пароль
            if ($this->provider == 'none'){
                //--- если не указан провайдер - обнулить токен, залогиниться, вывести флеш-сообщение, что провайдера нет
                Yii::$app->session->setFlash('error', 'Подключения к АПИ нет');
                return Yii::$app->user->login($this->user, $this->rememberMe ? 3600 * 24 * 30 : 0);
            } else {
                try{
                    $client = Yii::$app->authClientCollection->getClient($this->provider);
                    //-- запрашиваем у АПИ форму логина
                    $httpClient = new Client();
                    $request = $httpClient->createRequest()
                        ->setMethod('GET')
                        ->setOptions([
                            'maxRedirects' => 0,
                        ])
                        ->setUrl($client->buildAuthUrl());
                    /*
http://xle-api-server/oauth2/auth/index
                    ?expand=email
                    &client_id=xapi
                    &response_type=code
                    &redirect_uri=http%3A%2F%2Fxle-api-client%2Fsite%2Flogin
                    &xoauth_displayname=My%20Application
                    &state=a1f62fb02f737781c736a4aca4597503ca7651ff72119ea7a87d6b0593a1ea6d
                     */
                    $response = $request->send();
                    if (200 == $response->headers['http-code']){
                        //-- если форма логина пришла - заполняем ее и отправляем
                        $document = \phpQuery::newDocumentHTML($response->content);
                        $_csrf = $document->find('input[type=hidden]')->attr('value');
                        $cookies = $response->cookies;
                        foreach ($cookies as $key => $cookie) {
                            $cookies[$key]->value = urlencode($cookies[$key]->value);
                        }
                        $loginRequest = $httpClient->createRequest()
                            ->setMethod('POST')
                            ->setCookies($cookies)
                            ->setOptions([
                                'maxRedirects' => 0,
                            ])
                            ->setData([
                                '_csrf' => $_csrf,
                                'LoginForm' => [
                                    'username' => $this->username,
                                    'password' => $this->password,
                                ]
                            ])
                            ->setUrl($client->buildAuthUrl());
                        $response = $loginRequest->send();
                        $code200 = (200 == $response->headers['http-code']);
                        $code302 = (302 == $response->headers['http-code']);
                        if ( $code200 || $code302){
                            //-- если токен пришел
                            if (isset($response->headers['location'])){
                                $location = parse_url($response->headers['location']);
                                $params = [];
                                parse_str($location['query'], $params);
                                $_REQUEST['state'] = $params['state'];
                                $_GET['state'] = $params['state'];
                                $code = $params['code'];
                                //--- todo ОБРАБОТКА ТОКЕНА
                                try{
                                    $token = $client->fetchAccessTokenXle($code, [], $this->user);
                                } catch (\Exception $e){
                                    Yii::$app->session->setFlash('error', $e->getMessage()); //todo *********
                                    return false;
                                }
                                if ($token){
                                    Yii::$app->session->setFlash('success', 'Подключено к АПИ ' . $this->provider);
                                    Yii::$app->configs->apiProvider = $client->fullClientId;
                                    return Yii::$app->user->login($this->user, $this->rememberMe ? 3600 * 24 * 30 : 0);
                                } else {
                                    $this->errorContent = $response->content;

                                    $this->addError('username', 'Ошибка обработки токена'); //todo ********
                                    return false;
                                }
                            } else {
                                $this->errorContent = $response->content;

                                $this->addError('username', 'Неверная комбинация логина и пароля');
                                return false;
                            }
                        } else{
                            //-- если токен не пришел
                            $this->errorContent = $response->content;

                            $this->addError('username', 'токен не пришел'); //todo ********
                            return false;
                        }
                    } else {
                        //-- если форма логина не пришла
                        $this->errorContent = $response->content;

                        $this->addError('username', 'АПИ не прислал форму логина'); //todo ********
                        return false;
                    }


                } catch (\Exception $e){
                    $this->addError('username', $e->getMessage()); //todo ********
                    return false;
                }
            }


        }

        return false;

        //--- если указан провайдер
            //-- попытаться получить токен
            //-- если токен получен
                //-- обновить токен юсера, залогинить его и вывести флеш-сообщение про провайдера
            // если токен не получен
            //--- сбросить токен из БД и из сессии

    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

}
