Для работы с фоновыми задачами необходимо:
  Для работы с фоновыми задачами необходимо:
   1. Создать дублирующее подключение к основной базе $backGroundDb (backGroundDb.php), добавить его в web.php console.php
      (чтобы транзакции основного подключения не касались таблицы background_task)
   2. В params.php добавить пути к логам и временным файлам
       'pathToBackgroundTasksLogs' => '/runtime/logs/backgroundTasks/',
       'pathToBackgroundTasksTmpFiles' => '/runtime/logs/backgroundTasks/tmp/',
       'pathToAdditionsRealizationsResults' => '/runtime/logs/AdditionsRealizationsResults/tmp/',

   3. В params.php добавить
    'killBackgroundTaskAfterDone' => false/true, (не удалять задачи из БД автоматически после завершения - для отладки)
   4. Кроном чистить таблицу background_task от ошибок и зависших задач , старых фоновых задач в таблице (можно раз в день) (еще не сделано)
   5. Настроить logrotate для сжатия логов и удаления старых
  
  Фоновая задача запускается, web-приложением, как отдельный процесс на сервере
  Используется, когда:
  1. Необходимо выполнить операцию, занимающую много времени, а пользователя не хочется заставлять ждать.
   Для этого:
   - создать класс для исполнения фоновой задачи (пример - app\commands\backgroundTasks\tasks\TestTask),
     extends app\commands\backgroundTasks\models\TaskWorker, в котором переписать метод run
   - в Вашем контроллере запустить на исполнение фоновую задачу:
       подготовить ее параметры
           $model = TestTask::class;
           $arguments = ['id' => 777,];
       при необходимости, проверить наличие уже исполняемой фоновой задачи с такими параметрами
           $taskIsAlreadyRunning = BackgroundTask::taskIsRunning($model, $arguments);
       создать новую фоновую задачу
           $task = new BackgroundTask();
       запустить ее на исполнение и проверить результат запуска
           if ($task->startRun($model, $arguments)) {
               //успешный запуск
           } else {
               //ошибка при запуске
           }
   В местах Вашего web-приложения, где используются объекты, которые Вы изменяете в фоновой задаче,
   при необходимости, перед работой с ними из других екранных форм, можно проверять, запущена ли в данный момент
   какая-либо фоновая задача на их изменение, ($taskIsAlreadyRunning = BackgroundTask::taskIsRunning($model, $arguments);),
   и если да - то запрещать пользователю их изменение до окончания выполнения фоновой задачи
   По окончании выполнения тестовой задачи, ее статус будет TASK_STATUS_READY или TASK_STATUS_ERROR
 
  2. Необходимо выполнить операцию, занимающую много времени, заключенную в транзакцию, без перезагрузки страницы (аналог AJAX),
   и необходимо выводить на экран в режиме реального времени состояние выполнения этой операции и промежуточные результаты
   Для этого:
   - создать класс для исполнения фоновой задачи (пример - app\commands\backgroundTasks\tasks\TestTask),
     extends app\commands\backgroundTasks\models\TaskWorker, в котором переписать метод run
   - в контроллере определить $model (класс для исполнения фоновой задачи) и $arguments, передать их в представление
   - в представлении (view):
       передавать в представление $model (класс для исполнения фоновой задачи) и $arguments
       подключить app\assets\BackgroundTaskAsset::register($this)
       подготовить для js переменные
           $_arguments = Json::htmlEncode($arguments);
           $_model = addcslashes($model, '\\');
           $this->registerJs(
               "var _arguments = '{$_arguments}';"
               . 'var _model = "' . $_model . '";'
           ,\yii\web\View::POS_HEAD);
       нарисовать области для отображения состояния (см. комментарии app/assets/js/backgroundTask.js) 
       в js использовать объект BackgroundTask:
       <script>
           var params = {
               checkProgressInterval: 2000,
               urlStartBackgroundTask: '/background-tasks/start-task', (можно использовать свой контроллер, где переписать эти методы)
               urlGetTaskProgress: '/background-tasks/check-task',
               model: _model ,
               arguments: _arguments,
               taskStatusArea: $('#taskStatusArea'),
               resultArea: $("#addResultArea"),
               errorsArea: $("#addResultArea"),
               progressValueArea: $('#progressValueArea'),
               ajaxCounterArea: $("#ajaxCounterArea"),
               progressArea : $("#progressArea"),
               showTaskStatusArea : true,
               showProgressValueArea : true,
               showAjaxCounterArea : true,
               showResultArea : true,
               showProgressArea : true,
               showErrorsArea : true,
           };
           var bt = new BackgroundTask(params);
           bt.init();
 
           $("#btnStartAjax").on('click', function () {
               bt.start();
           });
       </script>
 
  3. Логирование.
   - логи про успешный старт и исполнение фоновых задач пишутся в $params['pathToBackgroundTasksLogs'] + LOG_SUCCESS_FILE_NAME
   - логи про ошибки исполнение фоновых задач пишутся в $params['pathToBackgroundTasksLogs'] + LOG_ERROR_FILE_NAME
   - если используется п. 2 и необходимо выводить на экран бегунок прогресса и временные результаты исполнения фоновой задачи,
     в методе, который непосредственно исполняет фоновую задачу (например app\commands\backgroundTasks\tasks\TestTask - public static function doTestBackgrounTask),
     и который принимает задачу в качестве параметра,  необходимо использовать цикл, заключенный в транзакцию,
     в котором переодически делать $task->setProgress($progressPercentCount); и $task->writeTemporaryResultToFile($reportStr);
     writeTemporaryResultToFile дописывает во временный текстовый файл (в папке $params['pathToBackgroundTasksTmpFiles']) порцию промежуточных данных
     Метод BackgroundTask::checkTask($taskId), который вызывается в аякс-контроллере для проверки статуса задачи, если у задачи статус TASK_STATUS_READY
     или TASK_STATUS_ERROR, удаляет ее временный файл результата, если он есть.

  4. Отладка
  Для отладки надо в params.php установить 'killBackgroundTaskAfterDone' => false.
  Пользователь с ролью adminSystem может из веба смотреть список фоновых задач, промежуточные результаты, останавливать задачи.
  Роут /adminxx/background-tasks/index, можно добавить в меню.

 * todo ВАЖНО - фоновая задача запускается в консольном режиме и Yii::$app не yii\web\Application, некоторых компонентов нет,
 * todo user_id можно брать из таблицы background_tasks, там есть такое поле
