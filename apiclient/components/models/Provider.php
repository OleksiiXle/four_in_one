<?php

namespace app\components\models;

use common\components\DbMessageSource;
use common\models\MainModel;
use Yii;
use yii\db\Query;

class Provider extends MainModel
{
    const SCENARIO_INSTALL = 'install';
    const SCENARIO_UPDATE = 'update';

    static $propertyAttributes = [
        'class' => 'class',
        'clientId' => 'client_id',
        'clientSecret' => 'client_secret',
        'tokenUrl' => 'token_url',
        'authUrl'  => 'auth_url',
        'signupUrl'  => 'signup_url',
        'apiBaseUrl' => 'api_base_url',
        'scope' => 'scope',
        'stateStorage' => 'state_storage_class'
    ];

    /**
     * @return null
     */
    public function getProperties()
    {
        if ($this->_properties === null) {
            foreach (static::$propertyAttributes as $clientAttribute => $dbAttribute) {
                if (!empty($this->{$dbAttribute})) {
                    $this->_properties[$clientAttribute] = $this->{$dbAttribute};
                }
            }
        }
        return $this->_properties;
    }

    private $_properties = null;

    public function scenarios()
    {
        $ret[self::SCENARIO_INSTALL] = [
            'id', 'class', 'client_id', 'client_secret', 'token_url', 'auth_url',
            'signup_url', 'api_base_url', 'scope', 'state_storage_class',
        ];
        $ret[self::SCENARIO_UPDATE] = [
            'id', 'class', 'client_id', 'client_secret', 'token_url', 'auth_url',
            'signup_url', 'api_base_url', 'scope', 'state_storage_class',
        ];
        return $ret ;
    }

    public function rules()
    {
        $rules =  [
            [['name', 'class', ], 'required'],
            //-- integer
            [['id', 'created_at', 'updated_at', 'created_by', 'updated_by' ], 'integer'],
            //-- string
            [['name', ], 'string', 'min' => 3, 'max' => 50],
            [['scope', ], 'string', 'max' => 10000],
            [['class', 'client_id', 'client_secret', 'state_storage_class'], 'string', 'min' => 3, 'max' => 255],
            [['token_url', 'auth_url', 'signup_url', 'api_base_url'], 'string', 'min' => 3, 'max' => 500],
        ];
        switch ($this->scenario) {
            case self::SCENARIO_INSTALL:
                break;
            case self::SCENARIO_UPDATE:
                $ret[] =
                    [['client_id', 'client_secret',], 'required',];
                $ret[] =
                    [['token_url', 'auth_url', 'signup_url', 'api_base_url'], 'url',];
                break;
        }

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'class' => Yii::t('app', 'Класс'),
            'client_id' => Yii::t('app', 'ИД клиента'),
            'client_secret' => Yii::t('app', 'Секрет клиента'),
            'token_url' => Yii::t('app', 'Адрес получения токена'),
            'auth_url' => Yii::t('app', 'Адрес авторизации'),
            'signup_url' => Yii::t('app', 'Адрес регистрации'),
            'api_base_url' => Yii::t('app', 'Адрес АПИ'),
            'scope' => Yii::t('app', 'Окружение'),
            'state_storage_class' => Yii::t('app', 'Класс определения состояния'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'provider';
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if (!$this->validate()) {
            return false;
        }
/*
        switch (($this->scenario)) {
            case self::SCENARIO_INSTALL:
                if (empty($this->tokenUrl)) {
                    $this->tokenUrl = ($this->class)::tokenUrl;
                }
                break;
            case self::SCENARIO_UPDATE:
                break;
        }


        return false;

*/
        return parent::save(false); // TODO: Change the autogenerated stub
    }

    public static function getClientsList()
    {
        $providers = static::find()
            ->select(['client_id', 'name'])
            ->asArray()
            ->all();
        $ret = [];
        foreach ($providers as $provider) {
            $ret[$provider['client_id']] = $provider['name'];
        }
        return $ret;
    }
}
