const REFRESH_INTERVAL=5000;

function modalOpenBackgroundTask(id, mode) {
    var url = '/adminxx/background-tasks/modal-open-background-task?id=' + id  + '&mode=' + mode;
    var title;
    switch (mode) {
        case 'view':
            title = 'Фоновая задача';
            break;
        case 'delete':
            title = 'Підтвердження видалення';
            break;

    }

    $.ajax({
        url: url,
        type: "GET",
        success: function(response){
            showModal(1800,800, title, response);
           // $('#xModalContent').html(response);
        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown)            }
    });
}

function modalOpenBackgroundTaskDelete(id) {
    var url = '/adminxx/background-tasks/modal-open-background-task-delete-confirm?id=' + id + '&mode=delete';
    var title = 'Підтвердження видалення';

    $.ajax({
        url: url,
        type: "GET",
        success: function(response){
            showModal(1800,850, title, response);
           // $('#xModalContent').html(response);
        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown)            }
    });
}

function deleteBackgroundTask(id) {
    $.ajax({
        url: '/adminxx/background-tasks/background-task-delete',
        type: "POST",
        data: {'id' : id},
        dataType: 'json',
        beforeSend: function() {
            preloader('show', 'mainContainer', 0);
        },
        complete: function(){
            preloader('hide', 'mainContainer', 0);
            hideModal();
        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown);
        },
        success: function (response) {
            console.log(response);
            if (response['status']){
                $.pjax.reload({container:"#gridBackgroundTasks"});
            } else {
                objDump(response['data']);
                console.log(response['data']);
            }
        }
    });
}

function showLog(mode) {
    var url = '/adminxx/background-tasks/modal-open-background-task-logs?mode=' + mode;
    var title;
    switch (mode) {
        case 'success':
            title = 'Success logs';
            break;
        case 'error':
            title = 'Error logs';
            break;
        case 'deleteUnnecessaryTasks':
            title = 'Очистка зайвих завдань';
            break;

    }

    $.ajax({
        url: url,
        type: "GET",
        success: function(response){
            showModal(2400,850, title, response);
            // $('#xModalContent').html(response);
        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown)            }
    });
}


setInterval(function() {
   // if ($('#yii-debug-toolbar').is('div')) return;
    if ($('#filterZone').css('display') !== 'none') return;
    try {
        if ($("#gridBackgroundTasks").length > 0) {
            $.pjax.reload({container:"#gridBackgroundTasks"});
        }
    } catch (err) {
        console.error(err);
    }
}, REFRESH_INTERVAL);

