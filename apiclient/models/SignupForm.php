<?php
namespace app\models;

use app\modules\adminxx\models\UserData;
use common\models\UserM;
use Yii;
use yii\base\Model;
use app\models\User;
use yii\httpclient\Client;

/**
 * Signup form
 */
class SignupForm extends Model
{

    const USER_NAME_PATTERN           = '/^[А-ЯІЇЄҐа-яіїєґA-Za-z0-9\']+?$/u'; //--маска для нимени
    const USER_NAME_ERROR_MESSAGE     = 'Допустимы буквы. Двойные имена через тире'; //--сообщение об ошибке
    const USER_PASSWORD_PATTERN       = '/^[a-zA-Z0-9~!@#$%^&*()_-]+$/ui'; //--маска для пароля
    const USER_PASSWORD_ERROR_MESSAGE = 'Допустимы буквы, цифры, спецсимволы ~!@#$%^&*()_-'; //--сообщение об ошибке
    const DEFAULT_ROLE = 'user';

    public $username;
    public $email;
    public $password;
    public $rememberMe = true;

    public $first_name;
    public $middle_name;
    public $last_name;

    public $provider;
    public $errorContent = '';

    public $retypePassword;

    protected $_user = false;

    public function getIdentity() {
        return $this->_user;
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
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username' , 'email', 'first_name', 'middle_name', 'last_name', 'password', 'retypePassword'], 'required'],
            [['retypePassword'], 'compare', 'compareAttribute' => 'password'],
            [['first_name', 'middle_name', 'last_name',
                'email', 'provider'], 'string', 'max' => 100],
            [['username', 'password', 'retypePassword' ], 'match', 'pattern' => self::USER_PASSWORD_PATTERN,
                'message' => \Yii::t('app', self::USER_PASSWORD_ERROR_MESSAGE)],
            [['first_name', 'middle_name', 'last_name'],  'match', 'pattern' => self::USER_NAME_PATTERN,
                'message' => \Yii::t('app', self::USER_NAME_ERROR_MESSAGE)],



            ['username', 'trim'],
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'This email address has already been taken.'],

            [['password', 'retypePassword'], 'string', 'min' => 6, 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            //-- user
            'id' => 'ID',
            'username' => Yii::t('app', 'Логин'),
            'email' => Yii::t('app', 'Email'),

            //-- user_data
            'first_name' => Yii::t('app', 'Имя'),
            'middle_name' => Yii::t('app', 'Отчество'),
            'last_name' => Yii::t('app', 'Фамилия'),

            //---- служебные
            'password' => Yii::t('app', 'Пароль'),
            'retypePassword' => Yii::t('app', 'Подтверждение пароля'),
        ];
    }

    /**
     * Signs user up.
     *
     * @return bool whether the creating new account was successful and email was sent
     */
    public function signup($emailConfirm = false)
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $user = new User();

            $user->username = $this->username;
            $user->email = $this->email;
            $user->password = $this->password;
            if ($emailConfirm){
                $user->email_confirm_token = \Yii::$app->security->generateRandomString();
                $user->status = UserM::STATUS_WAIT;
            } else {
                $user->status = UserM::STATUS_ACTIVE;
            }
            $user->setPassword($this->password);
            $user->generateAuthKey();
            if ($user->save()) {
                $userData = new UserData();
                $userData->user_id = $user->id;
                $userData->first_name = $this->first_name;
                $userData->middle_name = $this->middle_name;
                $userData->last_name = $this->last_name;
                if (!$userData->save()){
                    foreach ($userData->getErrors() as $key => $err){
                        $this->addError('username', $err[0] );
                    }
                    $transaction->rollBack();
                    return false;
                }
                $auth = Yii::$app->authManager;
                $userRole = $auth->getRole(self::DEFAULT_ROLE);
                if (isset($userRole)){
                    $ret = $auth->assign($userRole, $user->id);
                    if (!$ret){
                        $this->addError('username', "Помилка призначення ролі ");
                        $transaction->rollBack();
                        return false;
                    }
                } else {
                    $this->addError('username', "Роль не знайдена");
                    $transaction->rollBack();
                    return false;
                }

            } else {
                $this->addErrors( $user->getErrors());
                $transaction->rollBack();
                return false;
            }
            $transaction->commit();

            if ($emailConfirm) {
                return $this->sendEmail($user);
            } else {
                $ret = Yii::$app->getUser()->login($user->getUser(), 3600 * 24 * 30/*$this->rememberMe ? 3600 * 24 * 30 : 0*/);
                return $ret;
            }
        } catch (\Exception $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            $this->addError('username', $e->getMessage());
            return false;
        }
    }

    public function getApiRegistration()
    {
        if (!$this->validate()) {
            return false;
        }

        if (empty($this->provider) || $this->provider == 'none'){
            $this->addError('provider', 'Provider is empty');
            return false;
        } else {
            try{
                $client = Yii::$app->authClientCollection->getClient($this->provider);
                //-- запрашиваем у АПИ форму signup
                $httpClient = new Client();
                $request = $httpClient->createRequest()
                    ->setMethod('GET')
                    ->setOptions([
                        'maxRedirects' => 0,
                    ])
                    ->setUrl($client->buildSignupUrl());
                $response = $request->send();
                if (200 == $response->headers['http-code']){
                    //-- если форма логина пришла - заполняем ее и отправляем
                    $document = \phpQuery::newDocumentHTML($response->content);
                    $_csrf = $document->find('input[type=hidden]')->attr('value');
                    $cookies = $response->cookies;
                    foreach ($cookies as $key => $cookie) {
                        $cookies[$key]->value = urlencode($cookies[$key]->value);
                    }
                    $signupRequest = $httpClient->createRequest()
                        ->setMethod('POST')
                        ->setCookies($cookies)
                        ->setOptions([
                            'maxRedirects' => 0,
                        ])
                        ->setData([
                            '_csrf' => $_csrf,
                            'SignupForm' => [
                                'username' => $this->username,
                                'password' => $this->password,
                                'first_name' => $this->first_name,
                                'middle_name' => $this->middle_name,
                                'last_name' => $this->last_name,
                                'email' => $this->email,
                            ]
                        ])
                        ->setUrl($client->buildSignupUrl());

                    $response = $signupRequest->send();
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
                            if (!$this->signup(false)) {
                                return false;
                            }
                            //--- todo ОБРАБОТКА ТОКЕНА
                            try{
                                $token = $client->fetchAccessTokenXle($code, [], $this->user);
                            } catch (\Exception $e){
                                Yii::$app->session->setFlash('error', $e->getMessage()); //todo *********
                                return false;
                            }
                            if ($token){
                                Yii::$app->session->setFlash('success', 'Подключено к АПИ ' . $this->provider);
                                //  Yii::$app->configs->apiProvider = $client->fullClientId;
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



        return true;
    }

    /**
     * Sends confirmation email to user
     * @param User $user user model to with email should be send
     * @return bool whether the email was sent
     */
    protected function sendEmail($user)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Account registration at ' . Yii::$app->name)
            ->send();
    }
}
