var tasksCounter = 0;
var tasksPool = {};
var defaultParams = {
    'checkForAlreadyRunning' : false,
    'taskStatusArea' : false,
    'customStatusArea' : false,
    'progressArea' : '#progressArea',
    'resultArea' : '#resultArea',
    'errorsArea' : '#errorsArea',
    'showPreloader' : function () {},
    'hidePreloader' : function () {}
};
var urlGetTasksPool = '/background-tasks/get-background-tasks-pool';
var _csrf = $('meta[name="csrf-token"]').attr("content");

/*
* Проверка, если в сессии есть фоновые задачи- запускаем их
 */
function startTasksFromPool() {
    $.ajax({
        url:urlGetTasksPool,
        type: "POST",
        data: {'_csrf' : _csrf} ,
        dataType: 'json',
        success: function(response){
            if (typeof response['result'] == "object") {
                for (var i in response['result']) {
                    for (var taskId in response['result'][i]) {
                        showBackgroundTask(taskId, response['result'][i][taskId])
                    }
                }
            } else {
                alert(response['result']);
            }
        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown);
        }
    });
}

/*
* Продолжение контроля очереднлй фоновой задачи из сессии
 */
function showBackgroundTask(id, params){
    tasksCounter++;
    var taskId = 'backgroundTask_' + tasksCounter;
    var backgroundWrapper = '#backgroundWrapper';
    var backgroundTaskArea = '#backgroundTaskArea_' + tasksCounter;
    var doneButon = '#doneButton_' + tasksCounter;
    tasksPool[taskId] = new BackgroundTask(params);
    tasksPool[taskId].setSerializedParams(id, params);
    tasksPool[taskId].params['backgroundWrapper'] = backgroundWrapper;
    tasksPool[taskId].params['backgroundTaskArea'] = backgroundTaskArea;
    tasksPool[taskId].params['doneButon'] = doneButon;
    tasksPool[taskId].params['progressArea'] = defaultParams['progressArea'] + '_' + tasksCounter;
    tasksPool[taskId].params['resultArea'] = defaultParams['resultArea'] + '_' + tasksCounter;
    tasksPool[taskId].params['errorsArea'] = defaultParams['errorsArea'] + '_' + tasksCounter;

    tasksPool[taskId].init();

    switch (tasksPool[taskId].params['windowMode']) {
        case 'modal':
            drawTaskWindow(tasksCounter, tasksPool[taskId].params['title']);
            //------------------------------------------------------------------ вывод на экран
            var winH = $(window).height();
            var winW = $(window).width();
            $(backgroundTaskArea)
                .css('width', parseInt(tasksPool[taskId].params['widht']))
                .css('top', 50)
                .css('left', winW / 2 - parseInt(tasksPool[taskId].params['widht']) / 2)
                .fadeIn();
            $(backgroundWrapper).fadeIn();

            //------------------------------------------------------------------ кнопка сброса задачи
            $(document).on('click', '#close-btn_' + tasksCounter, function () {
                $(backgroundWrapper).fadeOut();
                $(backgroundTaskArea).fadeOut();
                tasksPool[taskId].removeTask();
            });
            break;
        case 'popup':
            drawTaskPopupWindow();
            $(backgroundWrapper)
                .css('width', parseInt(tasksPool[taskId].params['widht']));
            addNextPopup(tasksCounter, tasksPool[taskId].params['title']);
            $(backgroundTaskArea).append(drawDoneButton(tasksCounter, tasksPool[taskId].params['doneScript']));
            $(document).on('click', '#close-btn_' + tasksCounter, function () {
                tasksPool[taskId].cleanAreas();
                tasksPool[taskId].removeTask();
            });
            try {
                $(backgroundWrapper).draggable();
            } catch (e) {
                
            }
            break;
    }
    tasksPool[taskId].resumeObservation(id);
}

/*
* Запуцск новой фоновой задачи
 */
function startNewBackgroundTask(params ){
    tasksCounter++;
    var taskId = 'backgroundTask_' + tasksCounter;
    var backgroundWrapper = '#backgroundWrapper';
    var backgroundTaskArea = '#backgroundTaskArea_' + tasksCounter;
    var doneButon = '#doneButton_' + tasksCounter;
    tasksPool[taskId] = new BackgroundTask(params);
    tasksPool[taskId].params = params;
    for (key in defaultParams) {
        tasksPool[taskId].params[key] = defaultParams[key];
    }
    tasksPool[taskId].params['backgroundWrapper'] = backgroundWrapper;
    tasksPool[taskId].params['backgroundTaskArea'] = backgroundTaskArea;
    tasksPool[taskId].params['doneButon'] = doneButon;
    tasksPool[taskId].params['progressArea'] = defaultParams['progressArea'] + '_' + tasksCounter;
    tasksPool[taskId].params['resultArea'] = defaultParams['resultArea'] + '_' + tasksCounter;
    tasksPool[taskId].params['errorsArea'] = defaultParams['errorsArea'] + '_' + tasksCounter;

    tasksPool[taskId].init();

    switch (tasksPool[taskId].params['windowMode']) {
        case 'modal':
            drawTaskWindow(tasksCounter, tasksPool[taskId].params['title']);
            //------------------------------------------------------------------ вывод на экран
            var winH = $(window).height();
            var winW = $(window).width();
            $(backgroundTaskArea)
                .css('width', widht)
                .css('top', 50)
                .css('left', winW / 2 - parseInt(tasksPool[taskId].params['widht']) / 2)
                .fadeIn();
            $(backgroundWrapper).fadeIn();
            //------------------------------------------------------------------ кнопка сброса задачи
            $(document).on('click', '#close-btn_' + tasksCounter, function () {
                $(backgroundWrapper).fadeOut();
                $(backgroundTaskArea).fadeOut();
                tasksPool[taskId].removeTask();
            });
            break;
        case 'popup':
            drawTaskPopupWindow();
            $(backgroundWrapper)
                .css('width', parseInt(tasksPool[taskId].params['widht']));
            addNextPopup(tasksCounter, tasksPool[taskId].params['title']);
            $(backgroundWrapper).draggable();
            $(backgroundTaskArea).append(drawDoneButton(tasksCounter,tasksPool[taskId].params['doneScript']));
            $(document).on('click', '#close-btn_' + tasksCounter, function () {
                tasksPool[taskId].cleanAreas();
                tasksPool[taskId].removeTask();
            });
            break;
    }
    tasksPool[taskId].start();
}

/*
* Нарисовать модальное окно и область фоновой задачм
 */
function drawTaskWindow(id, title) {
    var taskWindow = "" +
        '<div id="backgroundWrapper" class="background-wrapper-modal">\n' +
        '    <div id="backgroundTaskArea_' + id + '" style="display: none" class="background-area-modal">\n' +
        '        <div class="container-fluid">\n' +
        '            <div class="row">\n' +
        '                <div class="info-header">\n' +
        '                    <div class="col-lg-11">\n' +
        '                        <span id="info-header" class="info-header-text">' + title + '</span>\n' +
        '                    </div>\n' +
        '                    <div class="col-lg-1" align="right">\n' +
        '                        <button id="close-btn_' + id + '" class="btn-control glyphicon glyphicon-remove" ></button>\n' +
        '                    </div>\n' +
        '                </div>\n' +
        '            </div>\n' +
        '            <div class="row to-hide_' + id + '">\n' +
        '                <div class="col-lg-6">\n' +
        '                    <div class="infoArea" id="taskStatusArea_' + id + '"></div>\n' +
        '                </div>\n' +
        '                <div class="col-lg-6">\n' +
        '                    <div class="infoArea" id="customStatusArea_' + id + '"></div>\n' +
        '                </div>\n' +
        '            </div>\n' +
        '            <div class="row">\n' +
        '                <div class="col-lg-12">\n' +
        '                    <progress id="progressArea_' + id + '" max="100" value="0" style="width: 100%"></progress>\n' +
        '                </div>\n' +
        '            </div>\n' +
        '            <div class="row  to-hide_' + id + '">\n' +
        '                <div class="col-lg-12">\n' +
        '                    <div id="resultArea_'  + id + '" class="result-area" style="display: none"></div>\n' +
        '                </div>\n' +
        '            </div>\n' +
        '            <div class="row  to-hide_<?=$id?>">\n' +
        '                <div class="col-lg-12">\n' +
        '                    <div id="errorsArea_' + id + '" class="result-area" style="display: none"></div>\n' +
        '                </div>\n' +
        '            </div>\n' +
        '        </div>\n' +
        '    </div>\n' +
        '</div>\n';

    $('body').append(taskWindow);
}

/*
* Нарисовать обертку для пула фоновых задач - попапов
 */
function drawTaskPopupWindow() {
    if ($('#backgroundWrapper').length == 0) {
        var taskWindow = "" +
            '<div id="backgroundWrapper" class="background-wrapper-popup">\n' +
            '</div>\n';

        $('body').append(taskWindow);
    }
    $('#backgroundWrapper').show();
}

/*
* Добавить в обертку очередную задачу - попап
 */
function addNextPopup(id, title) {
    var nextPopup = "" +
        '    <div id="backgroundTaskArea_' + id + '" style="display: block" class="background-area-popup">\n' +
        '        <div class="container-fluid">\n' +
        '            <div class="row">\n' +
        '                <div class="info-header">\n' +
        '                    <div class="col-lg-11">\n' +
        '                        <span id="info-header" class="info-header-text">' + title + ' ' + id + '</span>\n' +
        '                    </div>\n' +
        '                    <div class="col-lg-1" align="right">\n' +
        '                        <button id="close-btn_' + id + '" class="btn-control glyphicon glyphicon-remove" ></button>\n' +
        '                    </div>\n' +
        '                </div>\n' +
        '            </div>\n' +
        '            <div class="row to-hide_' + id + '">\n' +
        '                <div class="col-lg-6">\n' +
        '                    <div class="infoArea" id="taskStatusArea_' + id + '"></div>\n' +
        '                </div>\n' +
        '                <div class="col-lg-6">\n' +
        '                    <div class="infoArea" id="customStatusArea_' + id + '"></div>\n' +
        '                </div>\n' +
        '            </div>\n' +
        '            <div class="row">\n' +
        '                <div class="col-lg-12">\n' +
        '                    <progress id="progressArea_' + id + '" max="100" value="0" style="width: 100%"></progress>\n' +
        '                </div>\n' +
        '            </div>\n' +
        '            <div class="row  to-hide_' + id + '">\n' +
        '                <div class="col-lg-12">\n' +
        '                    <div id="resultArea_'  + id + '" class="result-area" style="display: none"></div>\n' +
        '                </div>\n' +
        '            </div>\n' +
        '            <div class="row  to-hide_<?=$id?>">\n' +
        '                <div class="col-lg-12">\n' +
        '                    <div id="errorsArea_' + id + '" class="result-area" style="display: none"></div>\n' +
        '                </div>\n' +
        '            </div>\n' +
        '        </div>\n' +
        '    </div>\n';

    $('#backgroundWrapper').append(nextPopup);
}

/*
* Нарисовать в очерелном попапе кнопку ГОТОВО
 */
function drawDoneButton(id,doneScript) {
    var doneBtn = '<button id="doneButton_' + id + '" onclick="'+ doneScript + '" style="display: none">done</button>';
    return doneBtn;
}

/*
* Загрузить файл после окончания задачи
 */
function downloadFile(btn) {
  //  console.log(btn);
    var taskId = 'backgroundTask_' + btn.id.replace('doneButton_', '');
  //  console.log(taskId);
    tasksPool[taskId].cleanAreas();
    tasksPool[taskId].uploadResult(true, true, 'result');
}

startTasksFromPool();
