<?php

namespace app\modules\post\grids\filters;

use Yii;
use app\modules\post\models\Post;
use common\widgets\xgrid\models\GridFilter;

class PostFilter extends GridFilter
{
    public $queryModel = Post::class;

    public $user_id;
    public $type;
    public $content;
    public $name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $ownRules = [
            [['name', ], 'string', 'max' => 100],
        ];

        return array_merge(parent::rules(), $ownRules);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'name',
        ];
    }

    public function getCustomQuery()
    {
        $query = Post::find();
       // $query = null;

        return $query;
    }


    public function getQuery()
    {
        $query = $this->defaultQuery;

        if (!empty($this->name)){
            $query->andWhere(['LIKE', 'name', $this->name ]);
            $this->_filterContent .= Yii::t('app', 'name') . ' = ' . $this->name . '; ' ;
        }

       //   $r = $query->createCommand()->getSql();

        return $query;
    }

    /**
     * @return null|string
     */
    public function getFilterContent()
    {
        $this->_filterContent = '';
        if (!empty($this->name)){
            $this->_filterContent .= Yii::t('app', 'name') . ' = ' . $this->name . '; ' ;
        }

        return $this->_filterContent;
    }

}