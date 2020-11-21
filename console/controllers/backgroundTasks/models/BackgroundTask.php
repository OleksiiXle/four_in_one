<?php

namespace console\controllers\backgroundTasks\models;

use console\controllers\InitController;
use Yii;
use yii\db\ActiveRecord;
use common\helpers\DateHelper;
use common\helpers\FileHelper;
use common\helpers\Functions;

/**
 * Класс для работы с фоновыми задачами
 * Для работы с фоновыми задачами необходимо:
 *  1. Создать дублирующее подключение к основной базе $backGroundDb (backGroundDb.php), добавить его в web.php, console.php
 *     (чтобы транзакции основного подключения не касались таблицы background_task)
 *  2. В params.php добавить пути к логам и временным файлам
 *      'pathToBackgroundTasksLogs' => '/runtime/logs/backgroundTasks/',
 *      'pathToBackgroundTasksTmpFiles' => '/runtime/logs/backgroundTasks/tmp/',
 *      'pathToAdditionsRealizationsResults' => '/runtime/logs/AdditionsRealizationsResults/tmp/', (для оргштатки - храним протоколы реализации приказов отдельно и всегда
 *  3. Кроном чистить таблицу background_task от ошибок и зависших задач , старых фоновых задач в таблице (можно раз в день) (еще не сделано)
 *  4. Настроить logrotate для сжатия логов и удаления старых
 *
 * Фоновая задача запускается, web-приложением, как отдельный процесс на сервере
 * Используется, когда:
 * 1. Необходимо выполнить операцию, занимающую много времени, а пользователя не хочется заставлять ждать.
 *  Для этого:
 *  - создать класс для исполнения фоновой задачи (пример - app\commands\backgroundTasks\tasks\TestTaskWorker),
 *    extends app\commands\backgroundTasks\models\TaskWorker, в котором переписать метод run
 *  - в Вашем контроллере запустить на исполнение фоновую задачу:
 *      подготовить ее параметры
 *          $model = TestTask::class;
 *          $arguments = ['id' => 777,];
 *      при необходимости, проверить наличие уже исполняемой фоновой задачи с такими параметрами
 *          $taskIsAlreadyRunning = BackgroundTask::taskIsRunning($model, $arguments);
 *      создать новую фоновую задачу
 *          $task = new BackgroundTask::newTask($model, $arguments);
 *      запустить ее на исполнение и проверить результат запуска
 *          if ($task->startRun()) {
 *              //успешный запуск
 *          } else {
 *              //ошибка при запуске
 *          }
 *  В местах Вашего web-приложения, где используются объекты, которые Вы изменяете в фоновой задаче,
 *  при необходимости, перед работой с ними из других екранных форм, можно проверять, запущена ли в данный момент
 *  какая-либо фоновая задача на их изменение, ($taskIsAlreadyRunning = BackgroundTask::taskIsRunning($model, $arguments);),
 *  и если да - то запрещать пользователю их изменение до окончания выполнения фоновой задачи
 *  По окончании выполнения тестовой задачи, ее статус будет TASK_STATUS_READY или TASK_STATUS_ERROR
 *
 * 2. Необходимо выполнить операцию, занимающую много времени, заключенную в транзакцию, без перезагрузки страницы (аналог AJAX),
 *  и необходимо выводить на экран в режиме реального времени состояние выполнения этой операции и промежуточные результаты
 *  Для этого:
 *  - создать класс для исполнения фоновой задачи (пример - app\commands\backgroundTasks\tasks\TestTaskWorker),
 *    extends app\commands\backgroundTasks\models\TaskWorker, в котором переписать метод run
 *  - в контроллере определить $model (класс для исполнения фоновой задачи) и $arguments, передать их в представление
 *  - в представлении (view):
 *      передавать в представление $model (класс для исполнения фоновой задачи) и $arguments
 *      подключить app\assets\BackgroundTaskAsset::register($this)
 *      подготовить для js переменные
 *          $_arguments = Json::htmlEncode($arguments);
 *          $_model = addcslashes($model, '\\');
 *          $this->registerJs(
 *              "var _arguments = '{$_arguments}';"
 *              . 'var _model = "' . $_model . '";'
 *          ,\yii\web\View::POS_HEAD);
 *      нарисовать области для отображения состояния (см. комментарии app/assets/js/backgroundTask.js)
 *      в js использовать объект BackgroundTask:
 *      <script>
 *          var params = {
 *              checkProgressInterval: 2000,
 *              urlStartBackgroundTask: '/background-tasks/start-task', (можно использовать свой контроллер, где переписать эти методы)
 *              urlGetTaskProgress: '/background-tasks/check-task',
 *              model: _model ,
 *              arguments: _arguments,
 *              taskStatusArea: $('#taskStatusArea'),
 *              resultArea: $("#addResultArea"),
 *              errorsArea: $("#addResultArea"),
 *              progressValueArea: $('#progressValueArea'),
 *              ajaxCounterArea: $("#ajaxCounterArea"),
 *              progressArea : $("#progressArea"),
 *              showTaskStatusArea : true,
 *              showProgressValueArea : true,
 *              showAjaxCounterArea : true,
 *              showResultArea : true,
 *              showProgressArea : true,
 *              showErrorsArea : true,
 *          };
 *          var bt = new BackgroundTask(params);
 *          bt.init();
 *
 *          $("#btnStartAjax").on('click', function () {
 *              bt.start();
 *          });
 *      </script>
 *
 * 3. Логирование.
 *  - логи про успешный старт и исполнение фоновых задач пишутся в $params['pathToBackgroundTasksLogs'] + LOG_SUCCESS_FILE_NAME
 *  - логи про ошибки исполнение фоновых задач пишутся в $params['pathToBackgroundTasksLogs'] + LOG_ERROR_FILE_NAME
 *  - если используется п. 2 и необходимо выводить на экран бегунок прогресса и временные результаты исполнения фоновой задачи,
 *    в методе, который непосредственно исполняет фоновую задачу (например app\commands\backgroundTasks\tasks\TestTask - public static function doTestBackgrounTask),
 *    и который принимает задачу в качестве параметра,  необходимо использовать цикл, заключенный в транзакцию,
 *    в котором переодически делать
 *    - $task->setProgress($progressPercentCount);
 *    - $task->writeTemporaryResultToFile($reportStr);
 *    writeTemporaryResultToFile дописывает во временный текстовый файл (в папке $params['pathToBackgroundTasksTmpFiles']) порцию промежуточных данных
 *    Метод BackgroundTask::checkTask($taskId), который вызывается в аякс-контроллере для проверки статуса задачи, если у задачи статус TASK_STATUS_READY
 *    или TASK_STATUS_ERROR, удаляет ее временный файл результата, если он есть.
 * todo ВАЖНО - фоновая задача запускается в консольном режиме и Yii::$app не yii\web\Application, некоторых компонентов нет,
 * todo user_id можно брать из таблицы background_tasks, там есть такое поле
 * @property int $id
 * @property int $pid
 * @property int $user_id
 * @property string $model
 * @property string $arguments
 * @property string $status
 * @property string $custom_status
 * @property string $result_file
 * @property string $result_file_pointer
 * @property int $progress
 * @property string $result
 * @property string $datetime_create
 * @property string $datetime_update
 * @property int $time_limit
 */
class BackgroundTask extends ActiveRecord
{
    private $_worker;
    private $_taskResult;
    private $_taskLogSuccessFileFullName = null;
    private $_taskLogErrorFileFullName = null;

    private $_user;

    private $taskLogsFileDir = null;
    private $taskResultFileDir = null;
    private $basePath = null;

    public $_taskResultFileFullName = null;
    public $killBackgroundTaskAfterDone = false;

    private $_isRunning;
    private $_timeLimitExpired;
    private $_canBeRemoved;

    const TASK_STATUS_NEW = 'new';
    const TASK_STATUS_PROCESS = 'process';
    const TASK_STATUS_READY = 'ready';
    const TASK_STATUS_ERROR = 'error';
    const TASK_STATUS_NOT_FOUND = 'not_found';

    const LOG_ERROR_FILE_NAME =  'tasksError.log';
    const LOG_SUCCESS_FILE_NAME =  'tasksSuccess.log';

    public function init()
    {
        $params = \Yii::$app->params;
        if (\Yii::$app->id !== 'app-console' && isset($params['appAlias'])) {
            $this->basePath = str_replace($params['appAlias'], '', \Yii::$app->basePath);
        } else {
            $this->basePath = \Yii::$app->basePath;
        }

        $this->taskLogsFileDir = \Yii::$app->basePath . '/runtime/logs' .
            InitController::PATH_TO_BACKGROUND_TASKS_LOGS . '/';
        if (!is_dir($this->taskLogsFileDir)) {
            throw new \Exception("Error:  $this->taskLogsFileDir does not exists" );
        }
        if (!is_writable($this->taskLogsFileDir)) {
            throw new \Exception("Error:  $this->taskLogsFileDir is not writable" );
        }

        $this->taskResultFileDir = \Yii::$app->basePath . '/runtime/logs' .
            InitController::PATH_TO_BACKGROUND_TASKS_LOGS . '/tmp/';
        if (!is_dir($this->taskResultFileDir)) {
            throw new \Exception("Error:  $this->taskResultFileDir does not exists" );
        }
        if (!is_writable($this->taskResultFileDir)) {
            throw new \Exception("Error:  $this->taskResultFileDir is not writable" );
        }

        $this->killBackgroundTaskAfterDone = (isset($params['killBackgroundTaskAfterDone']))
            ? $params['killBackgroundTaskAfterDone']
            : true;

/*
        $this->taskResultFileDir = (isset($params['pathToBackgroundTasksTmpFiles']))
            ? \Yii::$app->basePath . $params['pathToBackgroundTasksTmpFiles']
            : \Yii::$app->basePath . '/runtime/logs/backgroundTasks/tmp/';
        if (!is_dir($this->taskResultFileDir)) {
            mkdir($this->taskResultFileDir, 0777, true);
        }

        $this->taskLogsFileDir = (isset($params['pathToBackgroundTasksLogs']))
            ? \Yii::$app->basePath . $params['pathToBackgroundTasksLogs']
            : \Yii::$app->basePath . '/runtime/logs/backgroundTasks/';
        if (!is_dir($this->taskLogsFileDir)) {
            mkdir($this->taskLogsFileDir, 0777, true);
        }
*/

        parent::init(); // TODO: Change the autogenerated stub
    }

    public static function newTask($model, $arguments)
    {
        $task = new self();
        $task->model = $model;
        $task->status = BackgroundTask::TASK_STATUS_NEW;
        $task->user_id = \Yii::$app->user->getId();
        $task->arguments = (!empty($arguments))
            ? json_encode($arguments)
            : '';
        $task->result_file = str_replace('\\', '_', $task->model)
            . '_' . $task->user_id
            . '_' . time();
        $task->save();

        return $task;
    }

    /**
     * Чтобы работать с background_tasks независимо от транзакций необходимо отдельное подключение к той же базе
     * что и Yii::$app->db
     * @return mixed|\yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->backGroundDb;
        /*
        if (isset(Yii::$app->backGroundDb)) {
            return Yii::$app->backGroundDb;
        } else {
            return Yii::$app->db;
        }
        */
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'background_task';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pid', 'user_id', 'progress', 'result_file_pointer', 'time_limit'], 'integer'],
            [['model'], 'required'],
            [['arguments', 'result'], 'string', 'max' => 64999],
            [['datetime_create', 'datetime_update'], 'safe'],
            [['model'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 10],
            [['result_file'], 'string', 'max' => 256],
            [['custom_status'], 'string', 'max' => 100],
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
            'model' => 'Класс модели',
            'arguments' => 'Аргументы',
            'status' => 'Статус по БД',
            'custom_status' => 'Пользовательский статус',
            'result_file' => 'Файл результата',
            'result_file_pointer' => 'Result File pointer',
            'progress' => 'Прогресс',
            'result' => 'Результат',
            'datetime_create' => 'Создана',
            'datetime_update' => 'Изменена',
            'time_limit' => 'Лимит времени (сек)',
        ];
    }

    /**
     * @return array
     */
    public static function getStatusesArray()
    {
        return [
            self::TASK_STATUS_NEW => self::TASK_STATUS_NEW,
            self::TASK_STATUS_PROCESS => self::TASK_STATUS_PROCESS,
            self::TASK_STATUS_READY => self::TASK_STATUS_READY,
            self::TASK_STATUS_ERROR => self::TASK_STATUS_ERROR,
        ];
    }

    //************************************************************** ПЕРЕОПРЕДЕЛЕННЫЕ МЕТОДЫ

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        $datetime_now = DateHelper::getDatetime();
        $this->datetime_update = $datetime_now;

        if ($this->isNewRecord) {
            $this->status = self::TASK_STATUS_NEW;
            $this->datetime_create = $datetime_now;
        }

        return parent::beforeValidate(); // TODO: Change the autogenerated stub
    }

    /**
     * Если в таске переопределены методы runErrorCallback() runSuccessCallback() , то они вызываются в соответствующих случаях
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        // Process callbacks
        if (isset($changedAttributes['status'])) {
            switch ($this->status) {
                case self::TASK_STATUS_ERROR:
                    $this->runErrorCallback();
                    break;
                case self::TASK_STATUS_READY:
                    $this->runSuccessCallback();
                    break;
            }
        }

        if (isset($changedAttributes['progress'])) {
            $this->runProgressCallback();
        }

        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

    //************************************************************** ГЕТТЕРЫ, СЕТТЕРЫ

    /**
     * @return mixed
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $query = (new \yii\db\Query)
                ->from('user')
                ->select('username')
                ->where(['id' => $this->user_id])
                ->one();
            $this->_user = ($query) ? $query['username'] : 'none';
        }
        return $this->_user;
    }

    /**
     * Получить исполнителя задачи, если его еще нет - создать и передать ему аргументы
     * Get task worker
     * @return TaskWorker
     */
    public function getWorker()
    {
        if ($this->_worker === null) {
            /** @var TaskWorker $worker */
            $this->_worker = new $this->model;
            $this->_worker->setArguments($this->arguments);
            $this->_worker->setTask($this);
        }

        return $this->_worker;
    }

    /**
     * Полное имя файла, куда будет писаться результат работы таска
     * @return null|string
     */
    public function getTaskResultFileFullName()
    {
        $tmp = 1;
        if ($this->_taskResultFileFullName === null) {
            $this->_taskResultFileFullName = $this->taskResultFileDir . $this->result_file . '.txt';
            $this->_taskResultFileFullName = FileHelper::getWritableLogFile($this->_taskResultFileFullName);
        }
        return $this->_taskResultFileFullName;
    }

    /**
     * Полное имя файла, куда будет писаться успешный лог таска
     * @return null|string
     */
    public function getTaskLogSuccessFileFullName()
    {
        if ($this->_taskLogSuccessFileFullName === null) {
            $this->_taskLogSuccessFileFullName = $this->taskLogsFileDir . self::LOG_SUCCESS_FILE_NAME;
            $this->_taskLogSuccessFileFullName = FileHelper::getWritableLogFile($this->_taskLogSuccessFileFullName);
        }
        return $this->_taskLogSuccessFileFullName;
    }

    /**
     * Полное имя файла, куда будет писаться не успешный лог таска
     * @return null|string
     */
    public function getTaskLogErrorFileFullName()
    {
        if ($this->_taskLogErrorFileFullName === null) {
            $this->_taskLogErrorFileFullName = $this->taskLogsFileDir . self::LOG_ERROR_FILE_NAME;
            $this->_taskLogErrorFileFullName = FileHelper::getWritableLogFile($this->_taskLogErrorFileFullName);
        }
        return $this->_taskLogErrorFileFullName;
    }

    /**
     * @return mixed
     */
    public function getIsRunning()
    {
        $this->_isRunning = self::checkPidIsRunning($this->pid, $this->id);
        return $this->_isRunning;
    }

    /**
     * @return mixed
     */
    public function getTimeLimitExpired()
    {
        $datetime_create = Functions::dateTimeToInt($this->datetime_create);
        $this->_timeLimitExpired = $datetime_create < time() - $this->time_limit;
        return $this->_timeLimitExpired;
    }

    /**
     * @return mixed
     */
    public function getCanBeRemoved()
    {
        $this->_canBeRemoved = ($this->status == static::TASK_STATUS_ERROR) || ($this->timeLimitExpired);
        return $this->_canBeRemoved;
    }

    public function getErrorsWithAttributesLabels()
    {
        $errorsArray = $this->getErrors();
        $ret = [];
        foreach ($errorsArray as $attributeName => $attributeErrors ){
            foreach ($attributeErrors as $attributeError)
                $ret[$this->getAttributeLabel($attributeName)] = $attributeError;
        }
        return $ret;
    }

    public function showErrors()
    {
        $ret = $lines = '';
        $header = '<p>' . Yii::t('yii', 'Please fix the following errors:') . '</p>';
        $errorsArray = $this->getErrorsWithAttributesLabels();
        foreach ($errorsArray as $attrName => $errorMessage){
            $lines .= "<li>$attrName : $errorMessage</li>";
        }
        if (!empty($lines)) {
            $ret = "<div>$header<ul>$lines</ul></div>" ;
        }

        return $ret;

    }


    //************************************************************** МЕТОДЫ ОПРЕДЕЛЕНИЯ СОСТОЯНИЯ ТАСКА
    /**
     * Возвращает статус таска
     * @param $id
     * @return string/boolean
     * @throws \yii\db\Exception
     */
    public static function getTaskStatus($id)
    {
        $strSQL = "
                SELECT * FROM  `" . self::tableName() ."`
                WHERE id = $id;
                ";
        $task = \Yii::$app->db->createCommand($strSQL)->queryOne();

        // $task = self::findOne(['id' => $id]);
        if (empty($task)) {
            return false;
        }

        return $task['status'];
    }

    /**
     * Проверяет, есть ли запущенные процессы по таскам с $model и $arguments
     * todo NB - запускать только, если по логике софта не должно быть запущено два идентичных таска одновременно
     * @param $model
     * @param array $arguments
     * @param bool $killIfError - убивать ошибки
     * @return bool
     */
    public static function taskIsRunning($model, $arguments=[], $killIfError = false)
    {
        $result = false;
        $taskArguments = json_encode($arguments);
        $tasks = self::find()
            ->where(['model' => $model, 'arguments' => $taskArguments])
            ->asArray()
            ->all();

        if (!empty($tasks)){
            foreach ($tasks as $task) {
                $thatTaskIsRunning = self::checkPidIsRunning($task['pid'], $task['id']);
                if ($thatTaskIsRunning) {
                    //-- процесс с таким pid сейчас работает
                    if ($killIfError && in_array($task['status'], [self::TASK_STATUS_ERROR, self::TASK_STATUS_READY])) {
                        //-- если нужно чистить ошибки
                        $ret = self::killTask($task['pid'], $task['status']);
                        if (!$ret) {
                            //-- усли не получилось завершить процесс
                            return true;
                        }
                    } else {
                        //-- если не нужно чистить ошибки
                        return true;
                    }
                }
            }
       }

        return $result;
    }

    //************************************************************** МЕТОДЫ ДЛЯ ЗАПУСКА И РАБОТЫ ТАСКА

    /**
     * Запускает через RunTaskController() на исполнение таск в фоновом (консольном) режиме, вызывается из внешнего контроллера
    * @return bool|int - ид запущенного таска
     */
    public function startRun()
    {
        $tmp = 1;

        $php = '/usr/bin/php'; // /var/www/xle/staff-api/backend

        $commandStr = $php . ' ' . $this->basePath . 'yii ' .
            'backgroundTasks' . DIRECTORY_SEPARATOR .
            'run-task --task_id=' . $this->id;
        // /usr/bin/php /var/www/xle/staff/yii backgroundTasks/run-task --id=5
// /usr/bin/php /var/www/xle/staff-api/backend/yii backgroundTasks/run-task --task_id=9
// /usr/bin/php /var/www/xle/staff-api/yii backgroundTasks/run-task --task_id=9
        $pid = null;

    //    $logErrorFile = FileHelper::getWritableLogFile(\Yii::$app->basePath . self::LOG_ERROR_FILE_NAME);

     //   $commandStr .= " 2>>{$this->taskLogErrorFileFullName} 1>>/dev/null  & echo $!;";
        $commandStr .= " 2>>{$this->taskLogErrorFileFullName} 1>>/dev/null  & echo $! &";
     // /usr/bin/php /var/www/xle/staff/yii backgroundTasks/run-task --task_id=52 2>>/var/www/xle/staff/runtime/logs/backgroundTasks/tasksError.log 1>>/dev/null  & echo $!;
        exec($commandStr, $output,$exitCode);

        $pid = (empty($output) and is_array($output)) ? null : reset($output);

        if ($exitCode === 0 && !empty($pid)) {
            $this->pid = $pid;
            $this->save();

            return true;
        } else {
           // $this->status = self::TASK_STATUS_ERROR;
          //  $this->result = "Task was not started, exit code: $exitCode";
          //  $this->save();

            return false;
        }
    }

    /**
     * Находит таск по ид, определяет, кто его будет исполнять и запускает на исполнение, вызывается из RunTaskController()
     * todo *** можно использовать для пошаговой отладки из WEB
     * @param $id integer ид таска
     */
    public static function runTask($id)
    {
        $output = '';
        $errors = '';
        $success = false;
        $task = self::findOne(['id' => $id]);
        if ($task !== null) {
            if (class_exists($task->model)) {
                $task->status = self::TASK_STATUS_PROCESS;
                $task->save();
                $output .= "Task {$id} start processed!" . "\n";

                /** @var TaskWorker $worker */
                $worker = $task->getWorker();

                ob_start();

                try {
            //        echo 'start worker' . PHP_EOL;
                    $success = $worker->run();
            //        echo (($success) ? 'ok' : 'fail') . PHP_EOL;
                } catch (\Exception $e) {
                    // If model threw error we mark this task as failed and save error to log
                    $success = false;
                    $errors .= 'Exception: ' . $e->getMessage() . "\n";
                    $errors .= 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n";
                }

                $output .= ob_get_contents();
                ob_end_clean();

                if ($success === false) {
                    // If we have started db transaction need rollback that transaction
                    if ($transaction = \Yii::$app->getDb()->getTransaction()) {
                        $transaction->rollBack();
                    }

                    if (empty($errors)) {
                        $errors = $worker->errorMessage;
                    }
                    if (method_exists($worker, 'getErrors')) {
                        $worker->getErrors($errors);
                    } else {
                        $task->result = $errors;
                    }
                    $task->status = self::TASK_STATUS_ERROR;
                } else {
                    $task->status = self::TASK_STATUS_READY;
                }

                //  $task->result_file = $task->saveResult();
                if ($task->killBackgroundTaskAfterDone) {
                    if (!$task->remove()) {
                        $resultOutput = $task->status
                        . PHP_EOL . $task->getFirstError('id');
                    }
                } else {
                    $task->save();
                    $resultOutput = $task->status;
                }
            } else {
                $errors .= 'Model or method is not exists!' . "\n";
            }
        } else {
            $errors .= 'Task not found!' . "\n";
        }
        $datetime = DateHelper::getDatetime();
        $data = "\n{$datetime}  Run task with Id: {$id}\n";
        $data .= $errors ? "ERRORS \n{$errors}\n" : '';
        $data .= "OUTPUT:\n{$output}\n";
        $data .= isset($resultOutput) ? "Result: {$resultOutput}\n" : null;

        $task->saveOutputAndErrorsToLogFile($success, $data);
    }

    /**
     * Проверяет таск и возвращает инфу о его статусе, прогрессе и очередную порцию помежуточного результата
     * если таск завершен (успешно или с ошибкой) - удаляет файл промежуточных результатов
     * @param $id
     * @return array
     * $result = [
     *   'status' => static::TASK_STATUS_NEW,
     *   'progress' => 0,
     *  'temporaryResult' => [
     *          [0] => 'Строка помежуточного результата 1'
     *          ....
     *          [20] => 'Строка помежуточного результата 20'
     *   ]
     * ];
     */
    public static function checkTask($id)
    {
        $result = [
            'taskId' => 0,
            'status' => static::TASK_STATUS_NOT_FOUND,
            'progress' => 0,
            'temporaryResult' => [],
            'result' => '',
        ];

        $task = self::findOne($id);
        if ($task !== null) {
            $result = [
                'taskId' => $id,
                'status' => $task->status,
                'custom_status' => $task->custom_status,
                'progress' => $task->progress,
                'result' => $task->result,
            ];

            switch ($task->status) {
                case static::TASK_STATUS_NEW:
                    break;
                case static::TASK_STATUS_PROCESS:
                    $result['temporaryResult'] = $task->readTemporaryResultFromFile();
                    break;
                case static::TASK_STATUS_READY:
                    $result['temporaryResult'] = $task->readTemporaryResultFromFile();
                    break;
                case static::TASK_STATUS_ERROR:
                    $result['temporaryResult'] = $task->readTemporaryResultFromFile();
                    $result['temporaryResult'] = str_replace($task->result, '', $result['temporaryResult']);
                    /*
                    $resultFileName = $task->taskResultFileFullName;
                    if (file_exists($resultFileName)) {
                        unlink($resultFileName);
                    }
                    */
                    break;
            }
        }

        return $result;
    }

    /**
     * Сохранение конечного отчета о работе таска в файл LOG_SUCCESS_FILE_NAME или LOG_ERROR_FILE_NAME
     * @param $success
     * @param $data
     */
    private function saveOutputAndErrorsToLogFile($success, $data)
    {
        $logFile = ($success)
            ? $this->taskLogSuccessFileFullName
            : $this->taskLogErrorFileFullName;
           // ? FileHelper::getWritableLogFile(\Yii::$app->basePath . self::LOG_SUCCESS_FILE_NAME)
         //   : FileHelper::getWritableLogFile(\Yii::$app->basePath . self::LOG_ERROR_FILE_NAME);
        file_put_contents($logFile, $data, FILE_APPEND);
    }

    /**
     * Изменение progress исполняемого таска
     * @param integer $progress
     * @return bool
     */
    public function setProgress($progress)
    {
        $this->progress = (int)$progress;
        if ($this->save()) {
            return true;
        }
        return false;
    }

    /**
     * Изменение custom_status исполняемого таска
     * @param string $customStatus
     * @return bool
     */
    public function setCustomStatus($customStatus)
    {
        $this->custom_status = (string)$customStatus;
        if ($this->save()) {
            return true;
        }
        return false;
    }

    /**
     * Изменение result исполняемого таска
     * @param string $customStatus
     * @return bool
     */
    public function setResult($result)
    {
        $this->result = (string)$result;
        if ($this->save()) {
            return true;
        }
        return false;
    }

    /**
     * Не используется пока. Set progress in percents. Actual update will be once in every additional percent interval of work done.
     * @deprecated
     * @param int $done
     * @param int $total
     * @param int $updatePercentInterval
     *
     * @return bool
     */
    public function setProgressInPercent($done, $total, $updatePercentInterval = 3)
    {
        if ($done >= $total) {
            return $this->setProgress(100);
        }

        $interval = (int)(($total / 100) * $updatePercentInterval);
        $interval = ($interval < 1) ? 1 : $interval;

        if ($done % $interval === 0) {
            $currentProgress = (int)($done / ($total / 100));
            return $this->setProgress($currentProgress);
        }

        return true;
    }

    /**
     * Возвращает содержимое файла, куда пишется результат работы таска
     * @return mixed result of task
     */
    public function getResult()
    {
        if ($this->_taskResult == null and $this->result_file) {
            $this->_taskResult = json_decode(file_get_contents($this->result_file), true);
        }

        return $this->_taskResult;
    }

    /**
     * Дописывает временный результат в файл результата  работы таска
     * @param $data
     * @return bool|int
     */
    public function writeTemporaryResultToFile($data)
    {
        $fileName = $this->getTaskResultFileFullName();
        $ret = file_put_contents($fileName, $data, FILE_APPEND);

        return $ret;
    }

    /**
     * Читает порцию временного результата работы из файла результата, начиная с result_file_pointer
     * после чего модифицирует result_file_pointer для чтения следующего куска
     * @return array
     */
    public function readTemporaryResultFromFile()
    {
        $result = [];
        $fileName = $this->getTaskResultFileFullName();
        if (file_exists($fileName)) {
            $handle = fopen($fileName, "r");
            if ($handle) {
                $cntStr = 0;
                while ($cntStr < $this->result_file_pointer && (($buffer = fgets($handle)) !== false)) {
                    $cntStr++;
                }

                while (($buffer = fgets($handle)) !== false) {
                    $result[] = $buffer;
                    $cntStr++;
                }
                fclose($handle);
                $this->result_file_pointer = $cntStr;
                $this->save();
            }
        }

        return $result;
    }

    /**
     * Устанавливает таску $id с статус TASK_STATUS_ERROR, в поле result таска пишет $errorMessage
     * @param $id
     * @param $errorMessage
     * @throws \yii\db\Exception
     */
    public static function setTaskError($id, $errorMessage)
    {
        $strSQL = "
                UPDATE `" . self::tableName() . "`
                      SET `status` = '" . self::TASK_STATUS_ERROR . "', `result` = '" . addslashes($errorMessage) . "'
                WHERE id = $id;
                ";
        $ret = \Yii::$app->db->createCommand($strSQL)->execute();

    }

    public function setTimeLimit($timeLimit)
    {
        $this->time_limit = $timeLimit;
        return $this->save();
    }

    //************************************************************** МЕТОДЫ ДЛЯ ОЧИСТКИ ТАСКОВ ИЗ КРОНА

    /**
     * Анализ заданий из БД, остановка зависших процессов,
     * удаление из БД:
     * - успешно выполненных заданий
     * - неверно завершенных заданий
     * - зависших заданий
     * очисткавременных файлов
     * todo запускать, при необходимости из крона
     */
    public static function deleteUnnecessaryTasks()
    {
        $statusReady = self::TASK_STATUS_READY;
        $statusError = self::TASK_STATUS_ERROR;
        $searchStr = "
            status <> '$statusError'
            AND 
            ( status = '$statusReady' OR datetime_create < DATE_ADD(NOW(), INTERVAL -time_limit SECOND ))
        ";
        $result = [
            'status' => true,
            'data' => '',
        ];
        $tasks = static::find()
            ->select('id')
            ->where($searchStr)
            ->indexBy('id')
            ->asArray()
            ->all();

        if (empty($tasks)) {
            return $result;
        }

        $tasksIds = array_keys($tasks);
        $result = self::removeTasks($tasksIds);

        return $result;
    }

    /**
     * Прооверяет, исполняется ли процесс по pid с проверкой наличия в cmd строки "task_id=$task_id"
     * @param int $pid Process id
     * @return bool
     */
    public static function checkPidIsRunning($pid, $task_id = 0)
    {
        if (!empty($pid)) {
            exec("ps -p $pid", $output);
            $result = (count($output) > 1);

          //  exec("ps -p $pid -o comm=", $output,$exitCode);
          //  $result = (count($output) == 1 && $output[0] === 'php');

          //  exec("cat /proc/$pid/cmdline", $output,$exitCode);
         //   $result = (count($output) == 1 && strpos($output[0], "task_id=$task_id"));

            return $result;
        }

        return false;
    }

    /**
     * Удаляет таск со статусом TASK_STATUS_ERROR, если таск подвис - завершает его процесс по pid
     * @param int $id
     * @param string $status
     * @return bool
     */
    public static function killTask($id, $status = self::TASK_STATUS_ERROR)
    {
        $task = self::findOne(['id' => $id]);
        if (!$task) {
            return false;
        }
        $task->status = $status;
        if (static::checkPidIsRunning($task->pid, $task->id)) {
            exec("kill $task->pid");
        }
        return $task->save();
    }

    /**
     * Останавливает и удаляет из БД таски
     * @param array/integer $taskIds
     * @return array
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function removeTasks($taskIds)
    {
        $tmp = 1;
        $result = [
            'status' => true,
            'data' => '',
        ];

        if (is_array($taskIds)) {
            $taskIdsToRemove = $taskIds;
        } else {
            $taskIdsToRemove[] = $taskIds;
        }

        foreach ($taskIdsToRemove as $taskId) {
            $task = static::findOne($taskId);
            if (!empty($task)) {
                if ($task->remove()){
                    $result['data'] .= "taskId = $taskId successfully removed" . PHP_EOL;
                } else {
                    $result['status'] = false;
                    $result['data'] .= "taskId = $taskId " . PHP_EOL . $task->getFirstError('id') . PHP_EOL;
                }
            } else {
                $result['status'] = false;
                $result['data'] .= "taskId = $taskId " . PHP_EOL . " not found" . PHP_EOL;
            }
        }

        return $result;
   }

    /**
     * Если таск в данный момент запущен, пытается его остановить, если получилось - удаляет запись о нем из БД,
     * удаляет файл промежуточного результата
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove($killFile = false)
    {
        $pid = $this->pid;
        $resultFileName = $this->taskResultFileFullName;

        if (static::checkPidIsRunning($this->pid, $this->id)) {
         //   $command = 'pkill -9 -f "/usr/bin/php /var/www/xle/test/yii backgroundTasks/run-task --task_id=' . $this->id . '"';
           // exec($command,$output,$exitCode);
            $this->killProcess();
            if (static::checkPidIsRunning($this->pid, $this->id)){
                $this->addError('id', "The process PID=$pid could not be stopped. Call your administrator.");
                return false;
            }
        }
        if (!$this->delete()) {
            $this->addError('id', "The process PID=$pid was stopped, but the record about it was not deleted. Call your administrator.");
            return false;
        }
        if ($killFile) {
            if (file_exists($resultFileName)) {
                try {
                    unlink($resultFileName);
                } catch (\Exception $e) {
                    $errorMessage = "The process PID=$pid was stopped, the record about it was deleted, but temporary file was not deleted. Call your administrator."
                        . PHP_EOL
                        . $e->getMessage();
                    $this->addError('id', $errorMessage);
                    return false;
                }
            }
        }


        return true;
    }

    public function killProcess()
    {
        $processCMD = '/usr/bin/php ' .  $this->basePath . 'yii ' .
            'backgroundTasks' . DIRECTORY_SEPARATOR . 'run-task --task_id=' . $this->id;
        exec("pkill -9 -f '$processCMD'");

        return true;
    }


    //************************************************************** КАЛЛБЕКИ ДЛЯ ПЕРЕОПРЕДЕЛЕНИЯ ДЕЙСТВИЙ ПРИ ИЗМЕНЕНИИ СТАТУСА ТАСКА

    /**
     * Run progress callback method
     */
    private function runProgressCallback()
    {
        $this->getWorker()->onProgress($this->progress);
    }

    /**
     * Run success callback method
     */
    private function runSuccessCallback()
    {
        $this->getWorker()->onSuccess();
    }

    /**
     * Run error callback method
     */
    private function runErrorCallback()
    {
        $this->getWorker()->onError();
    }

    //************************************************************** ДРУГИЕ МЕТОДЫ

    public static function getFullLogsNames()
    {
        $params = \Yii::$app->params;

        $taskLogsFileDir = (isset($params['pathToBackgroundTasksLogs']))
            ? \Yii::$app->basePath . $params['pathToBackgroundTasksLogs']
            : \Yii::$app->basePath . '/runtime/logs/backgroundTasks/';

        return [
          'logSuccessFileFullName' => $taskLogsFileDir . self::LOG_SUCCESS_FILE_NAME,
          'logErrorFileFullName' => $taskLogsFileDir . self::LOG_ERROR_FILE_NAME
        ];

    }
}
