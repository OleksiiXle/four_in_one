<?php

namespace common\widgets\xgrid\models;

use yii\base\Model;

abstract class GridFilter extends Model
{
    public $checkedIds = [];
    public $allRowsAreChecked =  false;
    public $actionWithChecked = false;
    public $showOnlyChecked;
    public $queryModel;
    public $_filterContent = null;
    private $_sqlPrefix;
    private $_defaultQuery;

    abstract public function getCustomQuery();
    abstract public function getQuery();

    public function rules()
    {
        return [
            [['checkedIds'], 'safe'],
            [[ 'showOnlyChecked', 'allRowsAreChecked', 'actionWithChecked'], 'boolean'],
        ];
    }

    /**
     * @return mixed
     */
    public function getDefaultQuery()
    {
        $this->_defaultQuery = $this->getCustomQuery();
        if (!$this->actionWithChecked) {
            if (!$this->allRowsAreChecked && $this->showOnlyChecked == '1' && !empty($this->checkedIds)) {
                $this->_defaultQuery
                    ->andWhere(['IN', "$this->sqlPrefix.id", $this->checkedIds]);
                $this->_filterContent .= Yii::t('app', 'Только отмеченные');
            }
        } else {
            if (!$this->allRowsAreChecked && !empty($this->checkedIds)) {
                $this->_defaultQuery
                    ->andWhere(['IN', "$this->sqlPrefix.id", $this->checkedIds]);
            }
        }
        return $this->_defaultQuery;
    }

    public function getFilterContent()
    {
        $this->_filterContent = '';
        $this->getQuery();

        return $this->_filterContent;
    }

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