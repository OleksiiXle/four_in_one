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
}
