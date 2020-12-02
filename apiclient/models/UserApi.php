<?php

namespace app\models;

use Yii;
use \yii\web\IdentityInterface;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class UserApi extends ActiveRecord  implements IdentityInterface
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
        return 'user_api';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_WAIT]],
        ];
    }

    /**
     * Finds user by [[username]]
     *
     * @return UserApi|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = self::findByUsername($this->username);
        }

        return $this->_user;
    }

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
       // return static::findOne(['token' => $token, 'status' => self::STATUS_ACTIVE]);
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
    }

    /**
     * Find use by email
     *
     * @param $email
     * @return static|null
     */
    public static function findByEmail($email) {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
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

    /**
     * Метод возвращает список разрешений залогиненого! на сервере пользователя
     *
     * @param bool|false $forceUpdate
     * @return null|array
     */
    public static function getPermissions($forceUpdate = false) {
        if ($forceUpdate || is_null(self::$_cachedPermissions)) {
            /** @var UpzapiV1Client $upzapi */
            $upzapi = \Yii::$app->upzapi;
            $result = $upzapi->callMethod('/rbac/user-permission-list');
            if ($result['status'] && !empty($result['data'])) {
                self::$_cachedPermissions = $result['data'];
            }
        }

        return self::$_cachedPermissions;
    }
}
