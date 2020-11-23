<?php

namespace app\models;

use app\helpers\DateHelper;
use Yii;
use yii\db\ActiveRecord;

class MainModel extends ActiveRecord
{
    //******************** допустимые символы текста названий, пунктов приказа и пр.
    const PATTERN_TEXT = '#^[А-ЯІЇЄҐа-яіїєґ0-9A-Za-z ().№ʼ,«»\'"\-:;/]+$#u';
    const PATTERN_TEXT_ERROR_MESSAGE =
        'Допустимі українські літери, латиниця, цифри, пробел, лапки, символи ( . , № ʼ \'  " « »  - : ; / )';

    public function beforeSave($insert)
    {
        if ($insert){
            $this->created_at = time();
            if ($this->hasAttribute('created_by')) {
                if (isset(\Yii::$app->user->id)) {
                    $this->created_by = \Yii::$app->user->id;
                } elseif(empty($this->created_by)) {
                    $this->created_by = 0;
                }
            }
        }
        $this->updated_at = time();
        if ($this->hasAttribute('updated_by')) {
            if (isset(\Yii::$app->user->id)) {
                $this->updated_by = \Yii::$app->user->id;
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

    public function validateNotEmpty($attribute, $params)
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

}
