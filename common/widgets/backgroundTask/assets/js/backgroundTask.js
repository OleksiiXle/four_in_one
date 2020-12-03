/**
 * Объект BackgroundTask
 * Cоздается на странице, когда без перезагрузки необходимо запустить фоновую задачу и отслеживать ее исполнение.
 * Аналог AJAX, только фоновая задача запускается, как отдельный процесс на сервере,
 * который можно заключить в транзакцию, и который или должен исполниться полностью, или не исполниться вообще
 * Фоновая задача, работая в цикле может переодически соодщать о состоянии выполнения (прогресс, кусок временного результата в виде текста
 *
 *
 * @property {object} params  - массив параметров зля инициализации BackgroundTask
 * в property могут быть установлены переменные и функции:
 * @property {integer} checkProgressInterval - пауза в запросах о состоянии задачи (миллисекунды)
 * @property {string} urlStartBackgroundTask - URL контроллера, который запускает фоновую задачу
 * @property {string} urlGetTaskProgress - URL контроллера, который возвращает состояние фоновой задачи
 * @property {string} model - модель, которая будет выполнять фоновую задачу (с неймспейсом)
 * @property {string} arguments - строка JSON с аргументами, которые будут переданы в модель
 * @property {string} _csrf
 *
 * @property {string} progressArea - селектор области экрана, где отображается прогресс выполнения задачи
 * @property {string} taskStatusArea - селектор области экрана, где отображается статус задачи
 * @property {string} customStatusArea - селектор области экрана, где отображается пользовательский статус задачи
 * @property {string} resultArea - селектор области экрана, где отображается промежуточный результат (куда дописываются куски текста)
 * @property {string} errorsArea - селектор области экрана, где отображается сообщение об ошибке (может совпадать с resultArea)
 * @property {string} progressValueArea - селектор области экрана, где отображается цифровое значение прогресса выполнения задачи в процентах
 * @property {string} ajaxCounterArea - селектор области экрана, где отображается цифровое значение счетчика запросов к серверу (для отладки)
 *
 * @property {boolean} showCustomStatusArea - показывать customStatusArea
 * @property {boolean} showTaskStatusArea - показывать taskStatusArea
 * @property {boolean} showProgressValueArea - показывать progressValueArea
 * @property {boolean} showAjaxCounterArea - показывать ajaxCounterArea
 * @property {boolean} showResultArea - показывать resultArea
 * @property {boolean} showErrorsArea - показывать errorsArea
 * @property {boolean} showProgressArea - показывать progressArea
 *
 * @property {integer} ajaxCounter - счетчик AJAX - запросов
 * @property {integer} scrollCounter - счетчик строк в resultArea для скроллинга
 *
 * @property {function} showPreloader() - вызывается при старте, показывает прелоадер, можно переписывать в params
 * @property {function} hidePreloader() - вызывается при окончании (успешном или по ошибке), скрывает прелоадер, можно переписывать в params
 * @property {function} setProgress(progress) - как изменять progressArea при изменении прогресса, можно переписывать в params, progress - прогресс в процентах
 * @property {function} doOnSuccesss(progress) - что делать при успешном завершении фоновой задачи
 * @property {function} doOnError(progress) - что делать при ошибке во время исполнения фоновой задачи
 *
 * не рекомендуется переписывать в property:
 * @property {function} init() - берет params и на его основе модифицирует свойства созданного объекта BackgroundTask
 * @property {function} start() - запуск исполнения фоновой задачи
 * @property {function} trackProgress() - рекурсивная проверка состояния выполнения задачи
 * @property {function} processLoadingResponse() - обработка пришедшего состояния задачи
 * @property {function} showSuccessResult() - дописывает в resultArea пришедшую новую порцию промежуточного результата
 * @property {function} showErrorResult() - дописывает в errorsArea пришедшее сообщение об ошибке
 * @property {function} scrollTo() - скролит resultArea на новую пришедшую строчку промежуточного результата
 *
 * пример:
 *
 * <script>
 * var params = {
 *       checkProgressInterval: 2000,
 *       urlStartBackgroundTask: '/orgstat/test/start-task',
 *       urlGetTaskProgress: '/orgstat/test/check-task',
 *       model: 'app\\commands\\backgroundTasks\\tasks\\TestTask',
 *       arguments: {'id' : 777},
 *
 *       taskStatusArea: $('#taskStatusArea'),
 *       resultArea: $("#addResultArea"),
 *       errorsArea: $("#addResultArea"),
 *       progressValueArea: $('#progressValueArea'),
 *       ajaxCounterArea: $("#ajaxCounterArea"),
 *       progressArea : $("#progressArea"),
 *
 *       showTaskStatusArea : true,
 *       showProgressValueArea : true,
 *       showAjaxCounterArea : true,
 *       showResultArea : true,
 *       showProgressArea : true,
 *       showErrorsArea : true,
 *   };
 *
 * var bt = new BackgroundTask(params);
 * bt.init();
 *
 * $("#startBackgroundTaskButton").on('click', function () {
 *    bt.start();
 * });
 *
 *</script>
 *
 * @return {object}
 */


function BackgroundTask(params) {
    this.params = params;

    this.task_id = 0;
    this.taskStatus = '';
    this.taskNeedsToRemove = false;
    this.mode = 'dev';
    this.checkForAlreadyRunning = false;
    this.checkProgressInterval = 2000;
    this.urlStartBackgroundTask = _BASE_URL + '/background-tasks/start-task';
    this.urlTestBackgroundTask = _BASE_URL + '/background-tasks/test-background-task';
    this.urlGetTaskProgress = _BASE_URL + '/background-tasks/check-task';
    this.urlUploadResult = _BASE_URL + '/background-tasks/upload-result';
    this.urlKillTask = _BASE_URL + '/background-tasks/kill-task';
    this.model = null;
    this.arguments = null;
    this._csrf = $('meta[name="csrf-token"]').attr("content");

    this.progressArea = null;
    this.taskStatusArea = null;
    this.customStatusArea = null;
    this.resultArea = null;
    this.errorsArea = null;
    this.progressValueArea = null;
    this.ajaxCounterArea = null;

    this.showTaskStatusArea = false;
    this.showCustomStatusArea = false;
    this.showProgressValueArea = false;
    this.showAjaxCounterArea = false;
    this.showResultArea = false;
    this.showErrorsArea = false;
    this.showProgressArea = false;

    this.ajaxCounter = 0;
    this.scrollCounter = 0;


    this.init = function (start=false) {
        for (key in this) {
            if (this.params[key] !== undefined) {
                this[key] = this.params[key];
            }
            /*
            if (typeof this[key] != 'function'){
                console.log(key + '=' + this[key]);
            }
            */
        }


        if (start) {
            this.start();
        }
    };

    this.showPreloader = function () {
        preloader('show', 'mainContainer', 0);
        $('html,body').css('cursor','wait');

    };

    this.hidePreloader = function () {
        preloader('hide', 'mainContainer', 0);
        $('html,body').css('cursor','default');
    };

    this.start = function () {
        var that = this;
        switch (that.mode) {
            case 'prod':
                $.ajax({
                    url: that.urlStartBackgroundTask + '?checkForAlreadyRunning=' + that.checkForAlreadyRunning,
                    type: "POST",
                    data: {
                        'model' : that.model,
                        'arguments' : that.arguments,
                        '_csrf' : that._csrf
                    } ,
                    dataType: 'json',
                    beforeSend: function() {
                        that.showPreloader();
                    },
                    success: function(response){
                       // console.log('start response:');
                      //  console.log(response);
                        that.taskStatus = response.status;
                        if (!that.taskNeedsToRemove && response.status != 'error' && response.status != 'not_found'){
                            that.task_id = response.taskId;
                            setTimeout(that.trackProgress(response.taskId, that.processLoadingResponse), that.checkProgressInterval);
                        } else {
                            //    console.log(response);
                            that.processLoadingResponse(response, that);
                          //  alert('errors');
                        }
                    },
                    error: function (jqXHR, error, errorThrown) {
                        that.hidePreloader();
                        alert('Error: model=' + that.model + ' arguments=' . that.arguments);
                        errorHandler(jqXHR, error, errorThrown);
                    }
                });
                break;
            case 'dev':
                $.ajax({
                    url: that.urlTestBackgroundTask,
                    type: "POST",
                    data: {
                        'model' : that.model,
                        'arguments' : that.arguments,
                        '_csrf' : that._csrf
                    } ,
                    dataType: 'json',
                    beforeSend: function() {
                        that.showPreloader();
                    },
                    success: function(response){
                        console.log(response);
                    },
                    error: function (jqXHR, error, errorThrown) {
                        that.hidePreloader();
                        alert('Error: model=' + that.model + ' arguments=' . that.arguments);
                        errorHandler(jqXHR, error, errorThrown);
                    }
                });
                break;
        }
    };

    this.trackProgress = function (taskId,  processResponse) {
        var that = this;
        return function () {
            $.ajax({
                url: that.urlGetTaskProgress,
                type: "POST",
                data: {'taskId' : taskId} ,
                dataType: 'json',
                success: function (response) {
                 //   console.log(response);
                    if (that.showAjaxCounterArea) {
                        that.ajaxCounter++;
                        $(that.ajaxCounterArea).html(that.ajaxCounter);
                    }
                    that.taskStatus = response.status;
                    response.wait = function() {
                        if (!that.taskNeedsToRemove && response.status != 'error' && response.status != 'not_found' && response.status != 'ready' ) {
                            setTimeout(that.trackProgress(taskId, processResponse), that.checkProgressInterval);
                        }
                    };
                    processResponse(response, that);
                },
                error: function (jqXHR, error, errorThrown) {
                    that.hidePreloader();
                    alert('Error: taskId=' + taskId);
                    errorHandler(jqXHR, error, errorThrown);
                }

            });
        };
    };

    this.processLoadingResponse = function (response, target) {
      //  console.log(response);
        if (target.showTaskStatusArea) {
            $(target.taskStatusArea).html(response.status);
        }
        if (target.showCustomStatusArea) {
            $(target.customStatusArea).html(response.custom_status);
        }
        switch (response.status) {
            case 'new':
                if (target.showProgressArea) {
                    target.setProgress(0);
                }
                if (target.showProgressValueArea) {
                    target.progressValueArea.html(0);
                }
                response.wait();
                break;
            case 'process':
                if (target.showProgressArea) {
                    target.setProgress(response.progress);
                }
                if (target.showResultArea && response.temporaryResult.length > 0) {
                  //  console.log(response);
                    target.showSuccessResult(response.temporaryResult);
                }
                if (target.showProgressValueArea) {
                    target.progressValueArea.html(response.progress);
                }
                response.wait();
                break;
            case 'ready':
                if (target.showProgressArea) {
                    target.setProgress(response.progress);
                }
                if (target.showResultArea) {
                    target.showSuccessResult(response.temporaryResult);
                }
                target.hidePreloader();
                target.doOnSuccesss(response);
                break;
            case 'error':
                if (target.showResultArea) {
                    target.showSuccessResult(response.temporaryResult);
                }
                if (target.showErrorsArea) {
                    target.showErrorResult(response);
                }
                target.hidePreloader();
                target.doOnError(response);
                break;
            case 'not_found':
                target.showErrorResult(response);
                target.hidePreloader();
                if (!target.taskNeedsToRemove) {
                    target.doOnNotFound();
                }
                break;
        }
    };

    this.setProgress = function (progress) {
        $(this.progressArea).val(progress);
    };

    this.showSuccessResult = function (temporaryResult) {
        var infoText;
        var that = this;
        $(that.resultArea).css('display', 'block');
        $(temporaryResult).each(function (i, result) {
            if ( (result.indexOf(ERROR_PREFIX) < 0))  {
                infoText = '<span id="i_' + (that.scrollCounter) + '">' +  result + '</span>';
                $(that.resultArea).append(infoText + '<br>');
                that.scrollCounter++;
            }
            if (that.showErrorsArea && result.indexOf(ERROR_PREFIX) > 0) {
                $(that.errorsArea).css('display', 'block').append(result.replaceAll(ERROR_PREFIX, '') + '<br>');
            }
        });
        this.scrollTo(this.resultArea, (this.scrollCounter-1));
    };

    this.showErrorResult = function (response) {
        var infoText = '<div style="color: red;">' +  response.result.replaceAll(ERROR_PREFIX, '') + '</div>';
        $(this.errorsArea).css('display', 'block').append(infoText + '<br>');
   };

    this.scrollTo = function (area, counter) {
     //   console.log("#i_" + counter);
        var destination = $("#i_" + counter);
        if (destination.length > 0){
            $(area).animate({
                scrollTop: $(destination).offset().top
            }, 500, 'linear');
        }
    };

    this.doOnSuccesss = function (response) {
      //  alert('Success')
    };

    this.doOnError = function (response) {
    };

    this.doOnNotFound = function (response) {
        alert('Task not found')
    };

    this.uploadResult = function (killTask, killFile, fileNameColumn) {
        var uploadUrl = this.urlUploadResult + '?taskId=' + this.task_id + '&killTask=' + killTask + '&killFile=' + killFile + '&fileNameColumn=' + fileNameColumn;
        document.location.href = uploadUrl;
    };

    this.cleanAreas = function () {
        if (this.progressArea !== null) {
            $(this.progressArea).val(0);
        }
        if (this.taskStatusArea !== null) {
            $(this.taskStatusArea).html('');
        }
        if (this.customStatusArea !== null) {
            $(this.customStatusArea).html('');
        }
        if (this.resultArea !== null) {
            $(this.resultArea).html('');
        }
        if (this.errorsArea !== null) {
            $(this.errorsArea).html('');
        }
        if (this.progressValueArea !== null) {
            $(this.progressValueArea).html('');
        }
        if (this.ajaxCounterArea !== null) {
            $(this.ajaxCounterArea).html('');
        }
    };

    this.removeTask = function () {
        var that = this;
        that.taskNeedsToRemove = true;
        $.ajax({
            url: that.urlKillTask,
            type: "POST",
            data: {'taskId' : that.task_id} ,
            dataType: 'json',
            complete: function(response){
                that.cleanAreas();
            },
            success: function (response) {
                 //  console.log(response);
            },
            error: function (jqXHR, error, errorThrown) {
                errorHandler(jqXHR, error, errorThrown);
                that.cleanAreas();
                alert('Error: taskId=' + taskId + ' murder failed');
            }
        });

    };

}
