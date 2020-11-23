<?php
namespace app\modules\adminxx\models\filters;

use app\commands\backgroundTasks\models\BackgroundTask;
use app\widgets\xlegrid\models\GridFilter;

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

    private $_filterContent;


    public function rules()
    {
        return [
            [['pid', 'user_id', 'result_file_pointer', 'progress'], 'integer'],
            [['arguments', 'result'], 'string'],
            [['datetime_create', 'datetime_update'], 'safe'],
            [['model'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 10],
            [['result_file'], 'string', 'max' => 256],
        ];
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

    public function getQuery($params = null)
    {
        $query = BackgroundTask::find();
        //   $e = $query->createCommand()->getSql();

        if (!$this->validate()) {
            return $query;
        }

        if (!empty($this->status)) {
            $query->andWhere(['status' => $this->status]);
        }


        return $query;

    }

    public function getFilterContent()
    {
        $this->_filterContent = '';

        if (!empty($this->status)) {
            $this->_filterContent .= ' Статус *' . $this->status . '*;' ;
        }

        return $this->_filterContent;
    }

}