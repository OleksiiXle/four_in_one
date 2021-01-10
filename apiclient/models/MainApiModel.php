<?php

namespace app\models;

use app\components\models\apiQueryModels\ApiActiveRecord;
use Yii;

class MainApiModel extends ApiActiveRecord
{
    //******************** допустимые символы текста названий, пунктов приказа и пр.
    const PATTERN_TEXT = '#^[А-ЯІЇЄҐа-яіїєґ0-9A-Za-z ().№ʼ,«»\'"\-:;/]+$#u';
    const PATTERN_TEXT_ERROR_MESSAGE =
        'Допустимі українські літери, латиниця, цифри, пробел, лапки, символи ( . , № ʼ \'  " « »  - : ; / )';

    public $apiClient = null;
    public $response = [];

    public $_reflectionClass = null;
    public $_userApiId = null;
    public $_shortName = null;
    public $_attributes = null;

    const NO_PROPERTY_ATTRIBUTES = [
        'apiClient',
        'response',
    ];

    public function init()
    {
        parent::init();
        $this->apiClient = \Yii::$app->xapi;
    }

    public function __get($name)
    {
        $attributeName = "_$name";
        if (!empty($this->{$attributeName})) {
            return $this->{$attributeName};
        }

        switch ($attributeName) {
            case '_reflectionClass':
                    $this->_reflectionClass = new \ReflectionClass(static::class);
                return $this->_reflectionClass;
            case '_shortName':
                    $this->_shortName = $this->reflectionClass->getShortName();
                return $this->_shortName;
            case '_userApiId':
                    $this->_userApiId = \Yii::$app->user->getApiUserId();
                return $this->_userApiId;
            case '_attributes':
                $properties = $this->reflectionClass->getProperties(\ReflectionMethod::IS_PUBLIC);
                $this->_attributes = [];
                foreach ($properties as $property) {
                    if (!in_array($property->name, static::NO_PROPERTY_ATTRIBUTES) && substr($property->name, 0, 1) != '_') {
                        $this->_attributes[] = $property->name;
                    }
                }

                return $this->_attributes;
        }
    }

    public function getAttributes($names = null, $except = [])
    {
        $result = [];
        if (!empty($names)) {
            $needle = (is_array($names)) ? $names : array($names);
            foreach ($this->attributes as $attributeName) {
                if (!empty($needle) && in_array($attributeName, $needle)) {
                    $result[$attributeName] = $this->{$attributeName};
                }
            }

            return $result;
        }

        if (!empty($except)) {
            foreach ($this->attributes as $attributeName) {
                if (!in_array($attributeName, $except)) {
                    $result[$attributeName] = $this->{$attributeName};
                }
            }

            return $result;
        }

        foreach ($this->attributes as $attributeName) {
            $result[$attributeName] = $this->{$attributeName};
        }

        return $result;
    }

    public function setAttributes($values, $safeOnly = true)
    {
        $data = (isset($values[$this->shortName])) ? $values[$this->shortName] : $values;
        foreach ($data as $key => $value) {
            if (in_array($key, $this->attributes)) {
                $this->{$key} = $value;
            }
        }
    }

    public function beforeSave($insert)
    {
        if ($insert){
            $this->created_at = time();
            $user_id = \Yii::$app->user->getApiUserId();
            if ($this->hasAttribute('created_by')) {
                if ($user_id) {
                    $this->created_by = $user_id;
                } elseif(empty($this->created_by)) {
                    $this->created_by = 0;
                }
            }
        }
        $this->updated_at = time();
        if ($this->hasAttribute('updated_by')) {
            if ($user_id) {
                $this->updated_by = $user_id;
            } elseif(empty($this->updated_by)) {
                $this->updated_by = 0;
            }
        }

        return parent::beforeSave($insert);
    }

    public function getErrorsWithAttributesLabels()
    {
        $errorsArray = $this->getErrors();
        $ret = [];
        foreach ($errorsArray as $attributeName => $attributeErrors ){
            foreach ($attributeErrors as $attributeError)
            $ret[$this->getAttributeLabel($attributeName)] = $attributeError;
        }
        return $ret;
    }

    public function showErrors()
    {
        $ret = $lines = '';
        $header = '<p>' . Yii::t('yii', 'Please fix the following errors:') . '</p>';
        $errorsArray = $this->getErrorsWithAttributesLabels();
        foreach ($errorsArray as $attrName => $errorMessage){
            $lines .= "<li>$attrName : $errorMessage</li>";
        }
        if (!empty($lines)) {
            $ret = "<div>$header<ul>$lines</ul></div>" ;
        }

        return $ret;

    }

    public function validateNotEmpty($attribute)
    {
        if (empty($this->$attribute)) {
            $this->addError($attribute, 'Необхідно заповнити ' . $this->attributeLabels()[$attribute]);
        }
    }

    public function getSimpleErrorsArray()
    {
        $errorsArray = $this->getErrors();
        $ret = [];
        foreach ($errorsArray as $attributeName => $attributeErrors ){
            foreach ($attributeErrors as $attributeError)
                $ret[] = $this->getAttributeLabel($attributeName) . ' - ' . $attributeError;
        }
        return $ret;
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        if ($clearErrors) {
            $this->clearErrors();
        }

        if (!$this->beforeValidate()) {
            return false;
        }

        $scenarios = $this->scenarios();
        $scenario = $this->getScenario();
        if (!isset($scenarios[$scenario])) {
            throw new InvalidArgumentException("Unknown scenario: $scenario");
        }

        if ($attributeNames === null) {
            $attributeNames = $this->activeAttributes();
        }

        $attributeNames = (array)$attributeNames;

        foreach ($this->getActiveValidators() as $validator) {
            $validator->validateAttributes($this, $attributeNames);
        }
        $this->afterValidate();

        return !$this->hasErrors();
    }


}
