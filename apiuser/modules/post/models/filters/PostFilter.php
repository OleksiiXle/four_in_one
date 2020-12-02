<?php
namespace apiuser\modules\post\models\filters;

use Yii;
use apiuser\modules\post\models\Post;
use common\widgets\xlegrid\models\GridFilter;

class PostFilter extends GridFilter
{
    public $queryModel = Post::class;

    public $user_id;
    public $created_at;
    public $username;
    public $target;
    public $type;
    public $typeMedia;
    public $name;
    public $content;
    public $nameMedia;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'target', 'type', 'typeMedia', 'created_at', 'updated_at'], 'integer'],
            [['content'], 'string'],
            [['name', 'nameMedia'], 'string', 'max' => 250],
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
            'typeMedia' => Yii::t('app', 'Тип медиа'),
            'nameMedia' => Yii::t('app', 'Название медиа'),
            'content' => Yii::t('app', 'Содержимое'),
            'created_at' => Yii::t('app', 'Создано'),
            'updated_at' => Yii::t('app', 'Изменено'),
        ];
    }

    public function getFilterContent()
    {
        if ($this->_filterContent === null) {
            $this->getQuery();
        }

        return $this->_filterContent;
    }

    public function getQuery()
    {
        $this->_filterContent = [];
        $query = Post::find();

        if (!empty($this->user_id)){
            $query->joinWith(['user']);
            $this->_filterContent[] = Yii::t('app', 'Пользователь') ;

        }

        if (!empty($this->typeMedia) || !empty($this->nameMedia) ){
            $query->joinWith(['postMedia']);
        }

        if (!empty($this->name)){
            $query->andWhere(['LIKE', 'post.name', $this->name ]);
            $this->_filterContent[] = Yii::t('app', 'Название') ;
        }

        if (!empty($this->type)){
            $query->andWhere(['post.type' => $this->type]);
            $this->_filterContent[] = Yii::t('app', 'Тип') ;
        }
       // $r = $query->createCommand()->getSql();

        return $query;
    }

    public function getDataForUpload()
    {
        return [
            'id' => [
                'label' => 'id',
                'content' => 'value'
            ],
            'content' => [
                'label' => 'content',
                'content' => 'value'
            ],
            /*
            'status' => [
                'label' => 'Статус',
                'content' => function($model)
                {
                    return ($model->status == UserM::STATUS_ACTIVE) ? 'active' : 'not active';
                }
            ],
            */
        ];
    }
}