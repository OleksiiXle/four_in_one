<?php

namespace backend\modules\adminxx\models\oauth;

use Yii;
use common\models\User;
use yii\db\ActiveRecord;
use common\helpers\Functions;

class OauthRefreshToken extends ActiveRecord
{
    private $_username;
    private $_expiresDataTime;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oauth2_refresh_token';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'refresh_token' => 'authorization_code',
            'client_id' => 'client_id',
            'user_id' => 'user_id',
            'expires' => 'expires',
            'scope' => 'scope',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        $this->_username = 'not found';
        if (!empty($this->user)) {
            $this->_username = $this->user->username;
        }

        return $this->_username;
    }


    /**
     * @return mixed
     */
    public function getExpiresDataTime()
    {
        $this->_expiresDataTime = Functions::intToDateTime($this->expires);
        return $this->_expiresDataTime;
    }
}
