<?php

namespace app\modules\adminxx\controllers;

use Yii;
use app\commands\backgroundTasks\models\BackgroundTask;
use app\commands\backgroundTasks\tasks\TestTaskWorker;
use common\components\conservation\ActiveDataProviderConserve;
use common\components\AccessControl;
use app\modules\adminxx\grids\BackgroundTaskGrid;
use app\controllers\MainController;
use yii\web\Response;

/**
 * Class BackgroundTasksController
 * @package app\modules\adminxx\controllers
 */
class BackgroundTasksController extends MainController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'index', 'modal-open-background-task',
                        'modal-open-background-task-delete-confirm', 'background-task-delete',
                        'modal-open-background-task-logs',
                        'start-background-task', 'run-background-task', 'run-background-task-ajax'
                    ],
                    'roles'      => ['adminSystem'],
                ],
            ],
        ];
        return $behaviors;
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
      //  $this->layout = '@app/modules/adminxx/views/layouts/adminxx.php';
        $grid = new BackgroundTaskGrid();
        if (Yii::$app->request->isPost) {
            Yii::$app->getResponse()->format = Response::FORMAT_HTML;
            return $grid->reload(Yii::$app->request->post());
        }
        return $this->render('index', [
            'grid' => $grid,
        ]);
    }

    /**
     * @return string
     */
    public function actionView($id)
    {
        $task = BackgroundTask::findOne($id);
        return $this->render('view', [
            'task' => $task,
        ]);
    }

    /**
     * @param $id
     * @param $mode
     * @return string
     */
    public function actionModalOpenBackgroundTask($id, $mode)
    {
        $task = BackgroundTask::findOne($id);
        switch ($mode){
            case 'view':
            case 'delete':
                  $taskResultFileFullName = $task->taskResultFileFullName;
                  $resultContent = (file_exists($taskResultFileFullName))
                    ? file_get_contents($taskResultFileFullName)
                    : '';
                $resultContent = (!empty($resultContent))
                    ? str_replace(PHP_EOL, '<br>', $resultContent)
                    : 'Results file not found';

                return $this->renderAjax('_form_background_task', [
                    'task' => $task,
                    'mode' => $mode,
                    'resultContent' => $resultContent,
                ]);
            case 'success':
                break;
            case 'error':
                break;
            case 'deleteUnnecessaryTasks':
                break;
        }
    }

    /**
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionBackgroundTaskDelete()
    {
        if (\Yii::$app->request->isPost){
            $_post = \Yii::$app->request->post();
            if (isset($_post['id'])){
                $this->result = BackgroundTask::removeTasks($_post['id']);
            }
        }
        return $this->asJson($this->result);
    }

    /**
     * @param $id
     * @param $mode
     * @return string
     */
    public function actionModalOpenBackgroundTaskLogs($mode)
    {

        if ($mode == 'deleteUnnecessaryTasks') {
            $result = BackgroundTask::deleteUnnecessaryTasks();
            $content = ($result['status'])
                ? '<h3>Зайвi завдання успiшно видаленi</h3><br>'
                : '<h3 style="color: red">Зайвi завдання видаленi з помилками</h3><br>';
            $content .= str_replace(PHP_EOL, '<br>', $result['data']);
        } else {
            $logsNames = BackgroundTask::getFullLogsNames();
            switch ($mode) {
                case 'success':
                    $logFile = $logsNames['logSuccessFileFullName'];
                    break;
                case 'error':
                    $logFile = $logsNames['logErrorFileFullName'];
                    break;
            }
            $content = (file_exists($logFile))
                ? file_get_contents($logFile)
               // ? $content = 'lokoko'
                : '';
            $content = (!empty($content))
                ? str_replace(PHP_EOL, '<br>', $content)
                : 'Log file not found';
        }

        return $this->renderAjax('_form_background_tasks_logs', [
            'content' => $content,
        ]);
    }

    //*********************************************************************************** TESTING

    public function actionStartBackgroundTask()
    {
     //   return $this->render('test');
        $model = TestTaskWorker::class;
        $arguments = [
            'id' => 777,
        ];

        $taskIsAlreadyRunning = BackgroundTask::taskIsRunning($model, $arguments);
        if ($taskIsAlreadyRunning) {
            //-- todo NB - запускать только, если по логике софта не должно быть запущено два идентичных таска одновременно
            $taskArguments = json_encode($arguments);
            $result =
                [
                    'status' => false,
                    'message' => 'Реалізація не почалась. Аналогичные процессы сейчас запущены и работают',
                    'data' => BackgroundTask::find()
                        ->where(['model' => $model, 'arguments' => $taskArguments])
                        ->asArray()
                        ->all(),
                ];
        } else {
            $task = BackgroundTask::newTask($model, $arguments);
            if ($task->startRun()) {
                $result = [
                    'status' => true,
                    'message' => 'Реалізація почалась ...',
                    'data' => $task->getAttributes(),
                    'taskId' => $task->id,
                    'modelId' => 777,
                ];
            } else {
                $result = [
                    'status' => false,
                    'message' => 'Реалізація не почалась',
                    'data' => $task->getErrors(),
                ];
            }
        }


        return $this->render('backgroundTask', [
            'mode' => 'Start background task processing without waiting of ending',
            'id' => $arguments['id'],
            'result' => $result,
            'model' => $model,
            'arguments' => $arguments,
        ]);
    }

    public function actionRunBackgroundTask()
    {
        $model = TestTaskWorker::class;
        $arguments = [
            'id' => 7745,
        ];

        $task = BackgroundTask::newTask($model, $arguments);

        if (!$task->hasErrors()) {
            $result = BackgroundTask::runTask($task->id);
        } else {
            $result = $task->getErrors();
        }

        return $this->render('backgroundTask', [
            'mode' => 'Run background task processing with waiting without AJAX',
            'id' => $arguments['id'],
            'result' => $result,
            'arguments' => $arguments,
            'model' => $model,

        ]);


    }

    public function actionRunBackgroundTaskAjax()
    {
        $arguments = [
            'id' => 7778,
        ];
        $model = TestTaskWorker::class;

        $result = [
            'status' => true,
            'data' => 'no error'
        ];

        return $this->render('backgroundTask', [
            'mode' => 'Run background task processing with waiting with AJAX',
            'id' => $arguments['id'],
            'result' => $result,
            'model' => $model,
            'arguments' => $arguments,
        ]);
    }


}