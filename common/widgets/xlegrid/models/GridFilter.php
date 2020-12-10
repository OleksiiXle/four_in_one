<?php

namespace common\widgets\xlegrid\models;

use yii\base\Model;

class GridFilter extends Model
{
    public $checkedIds = [];
    public $allRowsAreChecked;
    public $showOnlyChecked;
    public $queryModel;
    private $_sqlPrefix;
    public $_filterContent = [];

    /**
     * @return mixed
     */
    public function getSqlPrefix()
    {
        if ($this->_sqlPrefix === null) {
            $this->_sqlPrefix = ($this->queryModel)::tableName();
        }
        return $this->_sqlPrefix;
    }

    public function getQueryIds()
    {
        $this->showOnlyChecked = false;
        $queryIds = $this->getQuery()
            ->select([$this->sqlPrefix . ".id"])
            ->asArray()
            ->indexBy("id")
            ->all();
        $this->checkedIds = array_keys($queryIds);
        $this->showOnlyChecked = true;

        return $queryIds;
    }



    public function rules()
    {
        return [
            [['checkedIds'], 'safe'],
            [[ 'showOnlyChecked', 'allRowsAreChecked'], 'boolean'],
        ];
    }

    public function getQuery()
    {
        $query = ($this->queryModel)::find();
       //    $e = $query->createCommand()->getSql();

        return $query;
    }


    public function getDataForUpload()
    {
        return [
            'attribute' => [
                'label' => 'Attribute label',
                'content' => 'value'
            ],
            'callBack' => [
                'label' => 'Call back label',
                'content' => function($model)
                {
                    return ($model->id == 1) ? 'id = 1' : 'id != 1';
                }
            ],
        ];
    }

}