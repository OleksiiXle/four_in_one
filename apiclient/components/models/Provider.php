<?php

namespace app\components\models;

use common\components\DbMessageSource;
use common\models\MainModel;
use Yii;
use yii\db\Query;

class Provider extends MainModel
{
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
            [['token_url', 'auth_url', 'signup_url', 'api_base_url'], 'url',]
        ];

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('app', 'Имя'),
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
        $tmp = 1;
        $providers = static::find()
            ->select(['name'])
            ->asArray()
            ->all();
        $ret = [
            'none' => 'Без АПИ'
        ];
        foreach ($providers as $provider) {
            $ret[(string)$provider['name']] = $provider['name'];
        }
        return $ret;
    }
}
