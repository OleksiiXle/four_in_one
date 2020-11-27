<?php
namespace apiserver\models;

use Yii;
use common\models\MainModel;

class UserM extends MainModel
{

    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 0;
    const STATUS_WAITING_FOR_EMAIL_CONFIRM = 1;
    const STATUS_WAITING_FOR_INVITATION_CONFIRM = 2;

    const SCENARIO_SIGNUP_BY_API  = 'signup_by_api';

    const USER_NAME_PATTERN           = '/^[А-ЯІЇЄҐа-яіїєґA-Za-z0-9\']+?$/u'; //--маска для нимени
    const USER_NAME_ERROR_MESSAGE     = 'Допустимы буквы. Двойные имена через тире'; //--сообщение об ошибке
    const USER_PASSWORD_PATTERN       = '/^[a-zA-Z0-9~!@#$%^&*()_-]+$/ui'; //--маска для пароля
    const USER_PASSWORD_ERROR_MESSAGE = 'Допустимы буквы, цифры, спецсимволы ~!@#$%^&*()_-'; //--сообщение об ошибке
    const DEFAULT_ROLE = 'user';

    public $password;

    public $first_name;
    public $middle_name;
    public $last_name;

    public function scenarios()
    {
        $ret = parent::scenarios();
        $ret[self::SCENARIO_SIGNUP_BY_API] = [
            //------------------------------------------------------------------------- user
            'username', 'email', 'status',
            'created_at', 'updated_at', 'created_by', 'updated_by', 'password_hash',
            'password', 'retypePassword', 'password_reset_token', 'auth_key', 'rememberMe',
            //------------------------------------------------------------------------- user_data
            'first_name', 'middle_name', 'last_name',
        ];
        return $ret ;
    }

    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username' , 'email', 'first_name', 'middle_name', 'last_name', 'password'], 'required'],
            [['first_name', 'middle_name', 'last_name',
                'email'], 'string', 'max' => 100],
            [['username', 'password' ], 'match', 'pattern' => self::USER_PASSWORD_PATTERN,
                'message' => \Yii::t('app', self::USER_PASSWORD_ERROR_MESSAGE)],
            [['first_name', 'middle_name', 'last_name'],  'match', 'pattern' => self::USER_NAME_PATTERN,
                'message' => \Yii::t('app', self::USER_NAME_ERROR_MESSAGE)],
            ['username', 'trim'],
            ['username', 'unique', 'targetClass' => self::class, 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => self::class, 'message' => 'This email address has already been taken.'],

            [['password'], 'string', 'min' => 6, 'max' => 20],

            // username and password are both required
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
        ];
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }


    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

}