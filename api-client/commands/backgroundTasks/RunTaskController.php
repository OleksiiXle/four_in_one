<?php

namespace app\commands\backgroundTasks;

use app\commands\backgroundTasks\models\BackgroundTask;
use yii\console\Controller;

class RunTaskController extends Controller
{
    public $task_id;

    public function options($actionID)
    {
        $options = parent::options($actionID);
        switch ($actionID) {
            case 'index':
                $options[]='task_id';
                break;
        }
        return $options;
    }

    public function actionIndex()
    {
        try {
            if (empty($this->task_id)) {
                echo 'no $this->task_id for operation' . PHP_EOL;
                exit(0);
            }

            BackgroundTask::runTask($this->task_id);

        } catch (\Exception $e) {
            $errors = 'Exception: ' . $e->getMessage() . "\n";
            $errors .= 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n";
            echo $errors;
            BackgroundTask::setTaskError($this->task_id, $errors);
            return;
        }
        return;
    }
}