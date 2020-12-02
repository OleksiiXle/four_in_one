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
 * @property {function} doOnSuccess(progress) - что делать при успешном завершении фоновой задачи
 * @property {function} doOnError(progress) - что делать при ошибке во время исполнения фоновой задачи
 *
 * не рекомендуется переписывать в property:
 * @property {function} init() - берет params и на его основе модифицирует свойства созданного объекта BackgroundTask
 * @property {function} start() - запуск исполнения фоновой задачи
 * @property {function} trackProgress() - рекурсивная проверка состояния выполнения задачи
 * @property {function} responseProcessing() - обработка пришедшего состояния задачи
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

const ERROR_PREFIX = '*error*';
var fbody1 = "console.log('lokoko');";


function BackgroundTask(params) {
    this.params = params;

    this.taskId = 0;
    this.taskStatus = '';
    this.useSession = 'false' ;
    this.taskNeedsToRemove = false;
    this.mode = 'prod';
    this.title = 'Background Task';
    this.windowMode = 'popup';
    this.widht = 500;
    this.doneScript = 'downloadFile(this);'; //-- скрипт js, который вызывается по нажатию кнопки после завершения задачи

    this.checkForAlreadyRunning = false;
    this.checkProgressInterval = 2000;
    this.urlStartBackgroundTask = '/background-tasks/start-task';
    this.urlTestBackgroundTask = '/background-tasks/test-background-task';
    this.urlGetTaskProgress = '/background-tasks/check-task';
    this.urlUploadResult = '/background-tasks/upload-result';
    this.urlKillTask = '/background-tasks/kill-task';
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

    this.doOnSuccessTxt = null;
    this.doOnErrorTxt = null;
    this.doOnNotFoundTxt = null;
    this.showPreloaderTxt = null;
    this.hidePreloaderTxt = null;

    this.progress = 0;
    this.ajaxCounter = 0;
    this.scrollCounter = 0;
    this.backgroundWrapper = null;
    this.backgroundTaskArea = null;
    this.doneButon = null;

    /*
    * Инициализация параметров задачи, если start, то после - запуск задачи
     */
    this.init = function (start=false) {
        for (key in this) {
            if (this.params[key] !== undefined) {
                if (typeof this[key] != 'function'){
                    this[key] = this.params[key];
                    switch (key) {
                        case 'doOnSuccessTxt':
                            this.doOnSuccess = function (response) {
                                eval(this.doOnSuccessTxt);
                            };
                            break;
                        case 'doOnErrorTxt':
                            this.doOnError = function (response) {
                                eval(this.doOnErrorTxt);
                            };
                            break;
                        case 'doOnNotFoundTxt':
                            this.doOnNotFound = function (response) {
                                eval(this.doOnNotFoundTxt);
                            };
                            break;
                        case 'showPreloaderTxt':
                            this.showPreloader = function () {
                                eval(this.showPreloaderTxt);
                            };
                            break;
                        case 'hidePreloaderTxt':
                            this.hidePreloader = function () {
                                eval(this.hidePreloaderTxt);
                            };
                            break;
                    }
                } else {
                    switch (key) {
                        case 'doOnSuccess':
                        case 'doOnError':
                        case 'doOnNotFound':
                        case 'showPreloader':
                        case 'hidePreloader':
                            this[key] = this.params[key];
                            break;
                    }
                }
            }
            if (1 == 0) {
                console.log('Attributes:');
                for (key in this) {
                    if (typeof this[key] != 'function') {
                        console.log(key + '=' + this[key]);
                    }
                }
                console.log('Functions:');
                for (key in this) {
                    if (typeof this[key] == 'function') {
                        console.log(key + '=' + this[key]);
                    }
                }
            }
        }
        if (start) {
            this.start();
        }
    };

    /*
    *Включить лоадер при старте задачи
     */
    this.showPreloader = function () {
        preloader('show', 'mainContainer', 0);
        $('html,body').css('cursor','wait');

    };

    /*
    *Выключить лоадер после завершения задачи
     */
    this.hidePreloader = function () {
        preloader('hide', 'mainContainer', 0);
        $('html,body').css('cursor','default');
    };

    /*
    * Возобновление надлюдения за задачей, посстановленной из пула сессии
     */
    this.resumeObservation = function (taskId) {
        setTimeout(this.trackProgress(taskId, this.responseProcessing), this.checkProgressInterval);
    };

    /*
    *Запуск на старт задачи - аякс запрос на старт и в случае успеха - вызов trackProgress с задержкой checkProgressInterval
     */
    this.start = function () {
        var that = this;
        var startData = {
            'model' : that.model,
            'arguments' : that.arguments,
            '_csrf' : that._csrf
        };
        if (that.useSession) {
            startData['serializedParams'] = that.getSerializedParams();
        }

        switch (that.mode) {
            case 'prod':
                $.ajax({
                    url: that.urlStartBackgroundTask + '?checkForAlreadyRunning=' + that.checkForAlreadyRunning + '&useSession=' + that.useSession,
                    type: "POST",
                    data: startData,
                    dataType: 'json',
                    beforeSend: function() {
                        that.showPreloader();
                    },
                    success: function(response){
                       // console.log('start response:');
                      //  console.log(response);
                        that.taskStatus = response.status;
                        if (!that.taskNeedsToRemove && response.status != 'error' && response.status != 'not_found'){
                            that.taskId = response.taskId;
                            that.progress = response.progress;
                            setTimeout(that.trackProgress(response.taskId, that.responseProcessing), that.checkProgressInterval);
                        } else {
                            //    console.log(response);
                            that.responseProcessing(response, that);
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

    /*
    *Рекурсивная проверка состояния задачи, аякс - запрос, выход из векурсии - если задача завершена (успешно или с ошибкой), не найдена или подлежит удалению
     */
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
                    if (!that.taskNeedsToRemove && response.status != 'error' && response.status != 'not_found' && response.status != 'ready' ) {
                        that.progress = response.progress;
                        setTimeout(that.trackProgress(taskId, processResponse), that.checkProgressInterval);
                    }
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

    /*
    *Обработка ответа о состоянии задачи
     */
    this.responseProcessing = function (response, target) {
       // console.log(response);
        if (target.showTaskStatusArea) {
            $(target.taskStatusArea).html(response.status);
        }
        if (target.showCustomStatusArea) {
            $(target.customStatusArea).html(response.custom_status);
        }
        switch (response.status) {
            case 'new':
                if (target.showProgressArea) {
                    target.setProgress();
                }
                if (target.showProgressValueArea) {
                    target.progressValueArea.html(0);
                }
                break;
            case 'process':
                if (target.showProgressArea) {
                    target.setProgress();
                }
                if (target.showResultArea && response.temporaryResult.length > 0) {
                  //  console.log(response);
                    target.showSuccessResult(response.temporaryResult);
                }
                if (target.showProgressValueArea) {
                    target.progressValueArea.html(response.progress);
                }
                break;
            case 'ready':
                if (target.showResultArea) {
                    target.showSuccessResult(response.temporaryResult);
                }
                target.hidePreloader();
                if (target.showProgressArea) {
                    target.setProgress(response.progress);
                }

                target.doOnSuccess(response);

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

    /*
    *Обновление указателя прогресса выполнения задачи
     */
    this.setProgress = function () {
        $(this.progressArea).val(this.progress);
    };

    /*
    *Обработка успешного состояния задачи
     */
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

    /*
    *Обработка ошибки в состоянии задачи
     */
    this.showErrorResult = function (response) {
        var infoText = '<div style="color: red;">' +  response.result.replaceAll(ERROR_PREFIX, '') + '</div>';
        $(this.errorsArea).css('display', 'block').append(infoText + '<br>');
   };

    /*
    *Прокрутка области показа промежуточного результата
     */
    this.scrollTo = function (area, counter) {
     //   console.log("#i_" + counter);
        var destination = $("#i_" + counter);
        if (destination.length > 0){
            $(area).animate({
                scrollTop: $(destination).offset().top
            }, 500, 'linear');
        }
    };

    /*
    *Действи/я, если задача успешно завершена
     */
    this.doOnSuccess = function (response) {
      //  alert('Success')
    };

    /*
    *Действия, если задача завершена с ошибкой
     */
    this.doOnError = function (response) {
    };

    /*
    *Действия, если проверяемая задача не найдена
     */
    this.doOnNotFound = function (response) {
        alert('Task not found')
    };

    /*
    *Выгрузка файла результата задачи пользователю
     */
    this.uploadResult = function (killTask, killFile, fileNameColumn) {
        var uploadUrl = this.urlUploadResult + '?taskId=' + this.taskId + '&killTask=' + killTask + '&killFile=' + killFile + '&fileNameColumn=' + fileNameColumn;
        document.location.href = uploadUrl;
    };

    /*
    *Очистка всех информационных блоков и закрытие окна задачи
     */
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
        switch (this.windowMode) {
            case 'modal':
                if (this.backgroundWrapper !== null && this.backgroundTaskArea !== null) {
                    $(this.backgroundWrapper).fadeOut();
                    $(this.backgroundTaskArea).fadeOut();
                }
                break;
            case 'popup':
                $(this.backgroundTaskArea).remove();
                if ($('.background-area-popup').length == 0) {
                    $(this.backgroundWrapper).remove();
                }
                break;
        }
    };

    /*
    *Принудительное завершение задачи (вызывается из внешнего скрипта)
     */
    this.removeTask = function () {
        var that = this;
        that.taskNeedsToRemove = true;
        $.ajax({
            url: that.urlKillTask,
            type: "POST",
            data: {'taskId' : that.taskId} ,
            dataType: 'json',
            complete: function(response){
                that.cleanAreas();
            },
            success: function (response) {
                   console.log(response);
            },
            error: function (jqXHR, error, errorThrown) {
                errorHandler(jqXHR, error, errorThrown);
                that.cleanAreas();
                alert('Error: taskId=' + taskId + ' murder failed');
            }
        });

    };

    /*
    *Возвращает JSON с сериализованными параметрами себя
     */
    this.getSerializedParams = function () {
        var serializedParamsArray = [];
        for (key in this) {
            if (typeof this[key] != 'function' && key != 'params' && key != 'arguments'){
                serializedParamsArray.push({'name':key, 'value' : this[key] });
            }
        }

        return  JSON.stringify(serializedParamsArray);
    };

    /*
    *
     */
    this.setSerializedParams = function (taskId, serializedParams) {
     //   console.log(serializedParams);
        var serializedParamsArray = JSON.parse(serializedParams);
        this.params = {};
        var that = this;
        $(serializedParamsArray).each(function (index, value) {
            that.params[value['name']] = value['value'];
        });
        that.params['taskId'] = taskId;
     //   console.log(that.params);
    };
}
