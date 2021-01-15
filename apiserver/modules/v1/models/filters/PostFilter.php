<?php

namespace app\modules\v1\models\filters;

use common\helpers\Functions;
use Yii;
use apiserver\modules\v1\models\Post;
use common\widgets\xgrid\models\GridFilter;

class PostFilter extends GridFilter
{
    public $queryModel = Post::class;

    public $user_id;
    public $type;
    public $content;
    public $name;
    public $sort = [];
    public $offset = null;
    public $limit = null;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $data = Yii::$app->request->post();
        if (isset($data['filter'])) {
            $this->setAttributes($data['filter']);
        }

        if (isset($data['checkedIds'])) {
            $this->checkedIds = $data['checkedIds'];
        }

        if (isset($data['offset'])) {
            $this->offset = $data['offset'];
        }

        if (isset($data['limit'])) {
            $this->limit = (int)$data['limit'];
        }

        if (isset($data['sort'])) {
            $this->sort = $data['sort'];
        }
    }

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

    public function runClientProviderQuery()
    {
        $query = $this->getQuery();

        $totalCount = $query->count();

        if (!empty($this->sort)){
            $query->addOrderBy($this->sort);
        }

        if (!empty($this->limit)){
            $query->limit($this->limit);
        }

        if (!empty($this->offset)){
            $query->offset($this->offset);
        }
        $r = $query->createCommand()->getSql();
        Functions::log($r);

        $ret = [
            'totalCount' => $totalCount,
            'queryData' => $query->asArray()->all(),
        ];

        return $ret;

    }

}