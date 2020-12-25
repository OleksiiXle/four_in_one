<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use common\models\UserM;

class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 10;
    const STATUS_WAIT = 5;
   // const PASSWORD_RESET_TOKEN_EXPIRE = 3600;
    //const DEFAULT_ROLE = 'user';

    private $_user = false;

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_WAIT]],
            [['refresh_permissions'] , 'boolean'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserTokens()
    {
        return $this->hasMany(UserToken::className(), ['client_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserClient()
    {
        return $this->hasMany(UserM::className(), ['id' => 'id']);
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = self::findByUsername($this->username);
        }

        return $this->_user;
    }

    public function getApiPermissions($provider)
    {
        $apiPermissions = [];
        $user = $this->user;
        if ($user){
            $userToken = UserToken::findOne(['client_id' => $user->id, 'provider' => $provider]);
            if ($userToken){
                $apiPermissions =  $userToken->userPermissions;
            }
        }
        return $apiPermissions;
    }

//********************************************************************************* IDENTITY INTERFACE

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
      //  return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user for reset password
     *
     * @param string $data
     * @return static|null
     */
    public static function findForReset($data)
    {
        //return var_dump(stripos($data, '@'));
        if(stripos($data, '@')){
            return static::findByEmail(trim($data));
        }else{
            return static::findByUsername(trim($data));
        }
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
                'password_reset_token' => $token,
                'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = \Yii::$app->configs->passwordResetTokenExpire;
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * --- Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * --- Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    //*************************************************************************************************************
    public function needRefreshPermissions()
    {
        return $this->refresh_permissions;
    }

    public function dropRefreshPermissions()
    {
        $this->refresh_permissions = false;
        return $this->save();
    }

    public function refreshToken($provider, $tokenParams, $userProfile)
    {
        /*
         array (
  'id' => 1,
  'status' => 10,
  'username' => 'admin',
  'last_name' => 'Администратор',
  'first_name' => 'Главный',
  'middle_name' => 'Системный',
  'email' => 'admin1@email.com',
  'userRBAC' =>
  array (
    'superAdmin' => '',
    'user' => '',
    'menuAdminxMain' => '',
    'systemAdminxx' => '',
  ),
  'userRBACVersion' => 1571391117,
  'updated_at' => 1570772063,
)
         */
        $r=1;
        //-- обновляем токен
        $token = UserToken::findOne(['client_id' => $this->id, 'api_id' => $userProfile['id'], 'provider' => $provider]);
        if (!isset($token)){
            $token = new UserToken();
            $token->api_id = $userProfile['id'];
            $token->client_id = $this->id;
            $token->provider = $provider;
        }
        $token->setAttributes($tokenParams);
        if (!empty($userProfile['permissions'])){
            $token->userPermissions = $userProfile['permissions'];
        }
     //   $data = $token->getAttributes();
        if (!$token->save()){
            throw new \Exception($token->showErrors());
        }
        //-- обновляем профиль пользователя
        /*
        if (!empty($userProfile)){
            $userClient = UserM::findOne($this->id);
            $userClient->scenario = UserM::SCENARIO_UPDATE;
            if ((int) $userProfile['updated_at'] > $userClient->updated_at) {
                $userClient->setAttributes($userProfile);
                if (!$userClient->save()){
                    throw new \Exception($userClient->showErrors());
                }
            }
        }
        */
        return true;
        /*
         yii\authclient\OAuthToken::__set_state(array(
   'tokenParamKey' => 'access_token',
   'tokenSecretParamKey' => 'oauth_token_secret',
   'createTimestamp' => 1571381916,
   '_expireDurationParamKey' => NULL,
   '_params' =>
  array (
    'access_token' => '2E-0iK0fAGGpIicK5imbLg1Sww5Q-ND2bBx17SpE',
    'expires_in' => 3600,
    'token_type' => 'bearer',
    'scope' => NULL,
    'refresh_token' => 'NFrOXcSLUQr_0veqPkpk_AnzJCeAVuemlVkvXMsy',
  ),
))
         */


    }


}
