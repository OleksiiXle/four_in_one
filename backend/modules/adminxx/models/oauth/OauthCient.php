<?php

namespace backend\modules\adminxx\models\oauth;

use Yii;
use yii\db\ActiveRecord;

class OauthCient extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oauth2_client';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_uri' => 'redirect_uri',
            'grant_type' => 'grant_type',
            'scope' => 'scope',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'created_by' => 'created_by',
            'updated_by' => 'updated_by',
        ];
    }
}
