<?php

namespace apiadmin\modules\adminxx\models\form;

use Yii;
use common\helpers\AccessHelper;
use apiadmin\modules\adminxx\models\UserM;

/**
 * Login form
 */
class Login extends UserM
{

    public $username;
    public $password;
    public $rememberMe = false;
    public $reCaptcha;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['username', 'password'], 'match', 'pattern' => self::USER_PASSWORD_PATTERN,
                'message' => \Yii::t('app', self::USER_PASSWORD_ERROR_MESSAGE)],

            ['username',  'string', 'min' => 3, 'max' => 50],
            ['password',  'string', 'min' => 3, 'max' => 50],

            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
         //   [['reCaptcha'], \himiklab\yii2\recaptcha\ReCaptchaValidator::className(), 'secret' => '6LfU-p8UAAAAAJIytAMOw7CMnd8K5HmVaP0vT49-']
        ];
    }


    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        $r=1;
        if ($this->validate()) {
            $ret = Yii::$app->getUser()->login($this->getUser(), 3600 * 24 * 30/*$this->rememberMe ? 3600 * 24 * 30 : 0*/);

            AccessHelper::saveUserPermissionsToSession();
            Yii::$app->userProfile->getProfileFromDb();
            return $ret;
        } else {
            return false;
        }
    }

}
