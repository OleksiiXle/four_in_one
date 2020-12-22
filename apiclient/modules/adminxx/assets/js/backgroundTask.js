const REFRESH_INTERVAL=10000;
let noReload = false;

function modalOpenBackgroundTask(id, mode) {
    noReload = true;
    var url = _BASE_URL + '/adminxx/background-tasks/modal-open-background-task?id=' + id  + '&mode=' + mode;
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
            noReload = false;
            errorHandler(jqXHR, error, errorThrown)            }
    });
}

function deleteBackgroundTask(id) {
    $.ajax({
        url: _BASE_URL + '/adminxx/background-tasks/background-task-delete',
        type: "POST",
        data: {'id' : id},
        dataType: 'json',
        beforeSend: function() {
            preloader('show', 'mainContainer', 0);
        },
        complete: function(){
            preloader('hide', 'mainContainer', 0);
            hideModal();
            noReload = false;
        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown);
            noReload = false;
        },
        success: function (response) {
            //console.log(response);
            if (response['status']){
                //$.pjax.reload({container:"#gridBackgroundTasks"});
                useFilter();
            } else {
                objDump(response['data']);
                console.log(response['data']);
            }
        }
    });
}

function showLog(mode) {
    var url = _BASE_URL + '/adminxx/background-tasks/modal-open-background-task-logs?mode=' + mode;
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
            showModal(1800,800, title, response);
            // $('#xModalContent').html(response);
        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown)            }
    });
}

function hideModalBt() {
    noReload = false;
    hideModal();
}

setInterval(function() {
    // if ($('#yii-debug-toolbar').is('div')) return;
    if ($('#filterZone').css('display') !== 'none' || noReload) return;
    try {
      //  console.log('reload');
        useFilter();
    } catch (err) {
        console.error(err);
    }
}, REFRESH_INTERVAL);


