<?php

namespace app\widgets\xlegrid\models;

use yii\base\Model;

class GridFilter extends Model
{
  //  public $checkedIds = [];
    public $checkedIdsJSON = '{}';
    public $showOnlyChecked;
    public $queryModel;
    public $_filterContent = [];


    public function rules()
    {
        return [
            [['checkedIdsJSON'], 'string', 'max' => 1000],
            [[ 'showOnlyChecked'], 'boolean'],
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