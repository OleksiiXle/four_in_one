<?php

namespace app\widgets\restGrid\models;

use Yii;
use yii\base\Model;

class AutoFilter extends Model
{
    //******************** допустимые символы текста
    const PATTERN_TEXT = '#^[А-ЯІЇЄҐа-яіїєґ0-9A-Za-z ().№ʼ,«»\'"\-:;/]+$#u';
    const PATTERN_TEXT_ERROR_MESSAGE =
        'Допустимы буквы, цифры, пробел, ковычки, символы ( . , № ʼ \'  " « »  - : ; / )';

    const NO_PROPERTY_ATTRIBUTES = [
        'attributesRules',
        'attributesLabels',
        'attributesConditions',
        'attributesRenderTypes',
        'colsCount',
    ];

    public $_reflectionClass = null;
    public $_attributes = null;
    public $autoFilterAttributes = [];
    public $attributesRules = [];
    public $attributesLabels = [];
    public $attributesConditions = [];
    public $attributesRenderTypes = [];
    public $colsCount = 1;

    public function getFilter($filterArray)
    {
        $result = [];
        foreach ($filterArray as $item) {
            if (!empty($item['value'])) {
                $this->{$item['name']} = $item['value'];
                $result[$item['name']] = [
                    'value' => $item['value'],
                    'condition' => $this->attributesConditions[$item['name']],
                ];
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return $this->attributesRules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return $this->attributesLabels;
    }

    public function prepare($autoFilter)
    {
        /*
            'autoFilter' => [
                'attributes' => [
                    'name' => [
                        'col' => 1,
                        'label' => 'Название',
                        'type' => 'string',
                        'condition' => 'LIKE',
                        'renderType' => 'input',
                    ],
                    'id' => [
                        'col' => 2,
                        'type' => 'integer',
                        'condition' => '=',
                        'renderType' => 'input',
                    ],
                ],
                'rules' => [
                    [['id', 'type'], 'integer'],
                    [['name'], 'string', 'min' => 3, 'max' =>20],
                    [['name'], 'match', 'pattern' => AutoFilter::PATTERN_TEXT, 'message' => AutoFilter::PATTERN_TEXT_ERROR_MESSAGE,],
                ],
            ],
         */
        $tmp = 1;
        foreach ($autoFilter['attributes'] as $attributeName => $attributeProperties) {
            $this->{$attributeName} = null;
            $this->autoFilterAttributes[] = $attributeName;
            if (isset($attributeProperties['col'])) {
                $this->colsCount = (int)$attributeProperties['col'];
            }
            $this->attributesLabels[$attributeName] = (isset($attributeProperties['label']))
                ? $attributeProperties['label']
                : $attributeName;
            if (isset($attributeProperties['condition'])) {
                $this->attributesConditions[$attributeName] = $attributeProperties['condition'];
            }
            if (isset($attributeProperties['col'])) {
                $this->colsCount = (int)$attributeProperties['col'];
            }
            if (isset($attributeProperties['renderType'])) {
                $this->attributesRenderTypes[$attributeName] = [
                    'type' => $attributeProperties['renderType'],
                    'selectData' => (isset($attributeProperties['selectData']))
                        ? ($attributeProperties['selectData'])
                        : [],
                ];
            }
        }

        if (isset($autoFilter['rules'])) {
            $this->attributesRules = $autoFilter['rules'];
        }


    }

    public function __set($name, $value)
    {
        $this->$name = $value;
        return;
    }


    public function __get($name)
    {
        if (!empty($this->{$name})) {
            return $this->{$name};
        }
        $attributeName = "_$name";
        if (!empty($this->{$attributeName})) {
            return $this->{$attributeName};
        }

        switch ($attributeName) {
            case '_reflectionClass':
                if ($this->_reflectionClass === null) {
                    $this->_reflectionClass = new \ReflectionClass(static::class);
                }
                return $this->_reflectionClass;
            case '_shortName':
                    $this->_shortName = $this->reflectionClass->getShortName();
                return $this->_shortName;
                /*
           case '_attributes':
                $properties = $this->reflectionClass->getProperties(\ReflectionMethod::IS_PUBLIC);
                $this->_attributes = [];
                foreach ($properties as $property) {
                    if (!in_array($property->name, static::NO_PROPERTY_ATTRIBUTES) && substr($property->name, 0, 1) != '_') {
                        $this->_attributes[] = $property->name;
                    }
                }
                return $this->_attributes;
                */
        }
    }

    public function getAttributes($names = null, $except = [])
    {
        $result = [];
        foreach ($this->autoFilterAttributes as $autoFilterAttribute) {
            $result[$autoFilterAttribute] = $this->{$autoFilterAttribute};
        }

        return $result;
    }

    public function setAttributes($values, $safeOnly = true)
    {
        $data = (isset($values[$this->shortName])) ? $values[$this->shortName] : $values;
        $attributes = array_keys($this->getAttributes());
        foreach ($data as $key => $value) {
            if (in_array($key, $attributes)) {
                $this->{$key} = $value;
            }
        }
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
            $this->addError($attribute, 'Необходимо указать ' . $this->attributeLabels()[$attribute]);
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

    public function validate__($attributeNames = null, $clearErrors = true)
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
