<?php

namespace app\modules\adminxx\grids\filters;

use console\controllers\backgroundTasks\models\BackgroundTask;
use common\widgets\xgrid\models\GridFilter;

class BackgroundTaskFilter extends GridFilter
{
    public $queryModel = BackgroundTask::class;

    public $id;
    public $pid;
    public $user_id;
    public $model;
    public $arguments;
    public $status;
    public $result_file_pointer;
    public $result_file;
    public $progress;
    public $result;
    public $datetime_create;
    public $datetime_update;

    public function rules()
    {
        $ownRules = [
            [['pid', 'user_id', 'result_file_pointer', 'progress'], 'integer'],
            [['arguments', 'result'], 'string'],
            [['datetime_create', 'datetime_update'], 'safe'],
            [['model'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 10],
            [['result_file'], 'string', 'max' => 256],
        ];

        return array_merge(parent::rules(), $ownRules);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pid' => 'Pid',
            'user_id' => 'User ID',
            'model' => 'Model',
            'arguments' => 'Arguments',
            'status' => 'Status',
            'result_file' => 'Result File',
            'result_file_pointer' => 'Result File Pointer',
            'progress' => 'Progress',
            'result' => 'Result',
            'datetime_create' => 'Datetime Create',
            'datetime_update' => 'Datetime Update',
        ];
    }

    public static function getStatusesArray()
    {
        return array_merge([
            '' => \Yii::t('app', 'Все')
        ], BackgroundTask::getStatusesArray());
    }

    public function getCustomQuery()
    {
        $query = BackgroundTask::find();

        return $query;
    }

    public function getQuery($params = null)
    {
        $query = BackgroundTask::find();
        if (!$this->validate()) {
            return $query;
        }

        if (!empty($this->status)) {
            $query->andWhere(['status' => $this->status]);
            $this->_filterContent .=  'Statuse "' . $this->status . '"; ' ;
        }
        //   $e = $query->createCommand()->getSql();

        return $query;
    }
}