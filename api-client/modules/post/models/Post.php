<?php

namespace app\modules\post\models;

use app\models\MainModel;
use app\modules\adminxx\models\UserM;
use Yii;

/**
 * This is the model class for table "post".
 *
 * @property int $id ID
 * @property int $user_id Владелец
 * @property int $target Цель
 * @property int $type Тип
 * @property string $name Название
 * @property resource $content Содержимое
 * @property int $created_at Создано
 * @property int $updated_at Изменено
 *
 * @property PostMedia[] $postMedia
 */
class Post extends MainModel
{
    const TYPE_FRONT = 1;
    const TYPE_TARGET = 2;
    const API_ROUTE_CREATE = '/post/create';

    public $user_id;
    public $type;
    public $content;
    public $name;

    public $response = [];

    public $apiClient = null;

    private $_shortName = null;

    public function __construct(array $config = [])
    {
        $this->apiClient = \Yii::$app->xapi;
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'target', 'type'], 'integer'],
            [['content'], 'string'],
            [['name'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'Владелец'),
            'target' => Yii::t('app', 'Цель'),
            'type' => Yii::t('app', 'Тип'),
            'name' => Yii::t('app', 'Название'),
            'content' => Yii::t('app', 'Содержимое'),
            'created_at' => Yii::t('app', 'Создано'),
            'updated_at' => Yii::t('app', 'Изменено'),
            //--------------------------------------------------------------------------- виртуальные атрибуты PostMedia

        ];
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        $tmp = 1;
        $data = [
            'user_id' => Yii::$app->user->getApiUserId(),
            'type' => self::TYPE_FRONT,
            'name' => $this->name,
            'content' => $this->content,
        ];

        $this->response = $this->apiClient->callMethod(self::API_ROUTE_CREATE, [], 'POST', $data);

        return $this->response['status'];
    }

    public static function findOne($condition)
    {
        $model = new self();


    }

    /**
     * @param null $names
     * @param array $except
     * @return array
     * @throws \ReflectionException
     */
    public function getAttributes($names = null, $except = [])
    {
        $class = new \ReflectionClass(self::class);
        $attributes = $class->getProperties(\ReflectionMethod::IS_PUBLIC);
        $result = [];
        if (!empty($names)) {
            $needle = (is_array($names)) ? $names : array($names);
            foreach ($attributes as $attribute) {
                $attributeName = $attribute->name;
                if (!empty($needle) && in_array($attributeName, $needle)) {
                    $result[$attributeName] = $this->{$attributeName};
                }
            }

            return $result;
        }

        if (!empty($except)) {
            foreach ($attributes as $attribute) {
                $attributeName = $attribute->name;
                if (!in_array($attributeName, $except)) {
                    $result[$attributeName] = $this->{$attributeName};
                }
            }

            return $result;
        }

        foreach ($attributes as $attribute) {
            $attributeName = $attribute->name;
            $result[$attributeName] = $this->{$attributeName};
        }

        return $result;
    }

    public function setAttributes($values, $safeOnly = true)
    {
        $attributes = array_keys($this->getAttributes());
        $data = (isset($values[$this->shortName])) ? $values[$this->shortName] : $values;
        foreach ($data as $key => $value) {
            if (in_array($key, $attributes)) {
                $this->{$key} = $value;
            }
        }
    }

    public function __get($name)
    {
        switch ($name) {
            case 'shortName':
                $class = new \ReflectionClass(self::class);
                return $class->getShortName();
        }
    }
}
