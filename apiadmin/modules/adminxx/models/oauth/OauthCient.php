<?php

namespace apiadmin\modules\adminxx\models\oauth;

use common\models\MainModel;
use Yii;
use yii\db\ActiveRecord;

class OauthCient extends MainModel
{
    const CLIENT_ID_PATTERN           = '/^[А-ЯІЇЄҐа-яіїєґA-Za-z0-9\']+?$/u'; //--маска для нимени
    const CLIENT_ID_ERROR_MESSAGE     = 'Допустимы буквы. Двойные имена через тире'; //--сообщение об ошибке

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
    public function rules()
    {
        $rules = [
            [['client_id', ], 'unique'],
            [['redirect_uri', ], 'url'],
            [['client_id', 'client_secret', 'redirect_uri', 'grant_type', ], 'required'],
            [['created_at', 'updated_at', 'created_by', 'updated_by', ], 'integer'],
            [['client_id', 'client_secret' ], 'string', 'max' => 80],
            [['redirect_uri', 'grant_type', 'scope' ], 'string', 'max' => 100000],
            [['client_id',  'client_secret', 'grant_type', 'scope'],  'match', 'pattern' => self::CLIENT_ID_PATTERN,
                'message' => Yii::t('app', self::CLIENT_ID_ERROR_MESSAGE)],
        ];
        return $rules;
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
