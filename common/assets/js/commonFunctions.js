function updateModel(modelName, formId, urlName, container_id ){
    //   alert('update -> ' + modelName + ' ' + formId + ' ' + urlName + ' container=' + container_id);
    // var qq=$("#" + formId).serialize();
    //  objDump(qq);
    $.ajax({
        url: _BASE_URL + urlName ,
        type: "POST",
        data: $("#" + formId).serialize(),
        dataType: 'json',
        beforeSend: function() {
            preloader('show', 'mainContainer', 0);
        },
        complete: function(){
            preloader('hide', 'mainContainer', 0);
        },
        success: function(response){
           // var result = JSON.parse( response);
            if (response['status']){
                $('#main-modal-lg').modal('hide');
                $('#main-modal-md').modal('hide');
                if (container_id != 'none') {
                    $.when(
                        $.pjax.reload({container:"#" + container_id})
                    ).done(function(){
                        displayFlashMessage('Данні успішно збережені');
                    });
                } else {
                    displayFlashMessage('Данні успішно збережені');
                }
            } else {
                displayFlashMessage(objDumpStr(response['data']));
            }
        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown);
        }
    });
}


//******* открыть модальное окно и загрузить в него данные
$(function(){
    $(document).on('click', '.showModalButtonLG', function(){
        $('#main-modal-lg').modal('show')
            .find('#modalContent_lg')
            .load($(this).attr('value'));
        document.getElementById('modalHeader_lg').innerHTML = '<b>' + $(this).attr('title') + '</b>';
    });
});

//-- обработка ошибок после аякс запроса
//-- если 403 - в #flashMessage /views/layouts/commonLayout выводится соответствующее сообщение
function errorHandler(jqXHR, error, errorThrown){
    console.log('Помилка:');
    console.log(error);
    console.log(errorThrown);
    console.log(jqXHR);
    if (jqXHR['status']==403){
        //   alert('accessDeny');
        var flashMessage = '';
        flashMessage += '<div class="alert alert-danger alert-dismissible">' + 'Дія заборонена' +'</div>';
        $("#flashMessage").show('slow');
        $("#flashMessage").html(flashMessage);
        setTimeout(function() {
            $("#flashMessage").hide('slow');
        }, 5000);
        $("#main-modal-lg").modal("hide");
        $("#main-modal-md").modal("hide");
    }
}

//-- обработка ошибок после аякс запроса
//-- если 403 - в #flashMessage /views/layouts/commonLayout выводится соответствующее сообщение
function errorHandlerModal(xhrStatus, xhr, status){
    var flashMessage = '';
    switch (xhrStatus){
        case 200:
            return true;
            break;
        case 403:
            flashMessage += '<div class="alert alert-danger alert-dismissible">' + 'Дія заборонена' +'</div>';
            break;
        default:
            flashMessage += '<div class="alert alert-danger alert-dismissible">' + 'Системна помилка ' + xhrStatus +  status +'</div>';
            break;
    }
    $("#flashMessage").show();
    $("#flashMessage").html(flashMessage);
    setTimeout(function() {
        $("#flashMessage").hide();
    }, 5000);
    $("#main-modal-lg").modal("hide");
    $("#main-modal-md").modal("hide");
    console.log('Помилка:');
    console.log(status);
    console.log(xhr);
}

function errorHandler2(jqXHR, textStatus){
    console.log('Помилка:');
    console.log(textStatus);
    console.log(jqXHR);
    if (jqXHR['status']==403){
        //   alert('accessDeny');
        var flashMessage = '';
        flashMessage += '<div class="alert alert-danger alert-dismissible">' + 'Дія заборонена' +'</div>';
        $("#flashMessage").show();
        $("#flashMessage").html(flashMessage);
        setTimeout(function() {
            $("#flashMessage").hide();
        }, 3000);
        $("#main-modal-lg").modal("hide");
        $("#main-modal-md").modal("hide");
    }
}

function displayFlashMessage(msg) {
    var flashMessage = $("#flashMessage");
    var flashMessageContent = '';
    if (flashMessage.length > 0){
        flashMessageContent += '<div class="alert alert-danger alert-dismissible">' + msg +'</div>';
        flashMessage.show();
        flashMessage.html(flashMessageContent);
        setTimeout(function() {
            flashMessage.hide();
        }, 3000);
    }

}

function alert_xle(txt, title){
    var out_txt = objDumpStr(txt);
   // var out_title = (title != undefined) ? title : '';
    var dial = $('#dialog');
    var params = {
        closeText: 'Закрити',
        modal: true,
        top: '100px',
        title: (title != undefined) ? title : '',
        width: '30%',
        buttons: {
            'OK': function () {
                $('#dialog').dialog('close');
            }
        }
    };
    dial.html(out_txt);
    dial.dialog(params);
}

/**
 * !!! AJAX проверка блокировок
 * Неободимо постом передать $locks ('department_45365'), если несколько блокировок на проверку - перечислять через запяту, например - 'department,position'
 * @param locks
 * @param doLock - функция которая выполняется, если есть блокировки
 * @param doNoLock - функция которая выполняется, если нет блокировки
 * @return \yii\web\Response
 */
function checkLocks(locks, doLock, doNoLock) {
    $.ajax({
        url: _BASE_URL + '/main/check-locks' ,
        type: "POST",
        data: {'locks' : locks},
        dataType: 'json',
        success: function(response){
            if (response['status']) {
                doLock();
            } else {
                doNoLock();
            }
        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown);
        }
    });
}



function objDump(object) {
    var out = "";
    if(object && typeof(object) == "object"){
        for (var i in object) {
            out += i + ": " + object[i] + "\n";
        }
    } else {
        out = object;
    }
    alert(out);
}

//------------------------------
function objDumpStr(object) {
    var out = "";
    if(object && typeof(object) == "object"){
        for (var i in object) {
            out += i + ": " + object[i] + "<br>";
        }
    } else {
        out = object;
    }
    return out;
}

function setUserActivity() {
   // console.log(_user_id + ' ' + _user_action);
    if (_user_id !== undefined){
        $.ajax({
            url: _BASE_URL + '/site/set-user-activity',
            type: "POST",
            data: {
                'user_id' : _user_id,
                'user_action' : _user_action
            },
            dataType: 'json',
            success: function(response){
                //  console.log(response)
            },
            error: function (jqXHR, error, errorThrown) {
                errorHandler(jqXHR, error, errorThrown);
            }
        })
    }

}

function saveHistory()
{
    if ($('#yii-debug-toolbar').is('div')) return;
    try {
        setUserActivity();
    } catch (err) {
      //  console.error(err);
    }

    t=setTimeout('saveHistory()',50000);
   // alert(_user_id);
}

//-- показать/убрать прелоадер, parent- ид элемента после которого рисуется прелоадер, и который будет затухать
//-- id -порядковый номер прелоадера - чтобы не былдо конфликтов
function preloader(mode, parent, id) {
    var parentDiv = $("#" + parent);
    var preloader_id = 'preloaderXle' + id;
    switch (mode) {
        case 'show':
            parentDiv.append('<div id="' + preloader_id + '" class="loaderXle"></div>');
            parentDiv.removeClass('LockOff').addClass('LockOn');
            $("#" + preloader_id).removeClass('LockOn').addClass('LockOff');
            break;
        case 'hide':
            $("#" + preloader_id).remove();
            parentDiv.removeClass('LockOn').addClass('LockOff');
            break;
    }

}

//-- вывести ошибки валидации к неправильным полям после аякса в загруженную форму
//-- formModel_id-модель мелкими буквами, errorsArray - массив ошибок
function showValidationErrors(formModel_id, errorsArray) {
    /*
    <div class="form-group field-orderprojectdepartment-name required has-error">
<label class="control-label" for="orderprojectdepartment-name">Найменування</label>
<input type="text" id="orderprojectdepartment-name" class="form-control" name="OrderProjectDepartment[name]" autofocus="" onchange="$('#orderprojectdepartment-name_gen').val(this.value);" tabindex="1" aria-required="true" aria-invalid="true">

<div class="help-block">Необхідно заповнити "Найменування".</div>
</div>
     */

    if (typeof errorsArray == 'object' ){
        var attrInput;
        var errorsBlock;
        var formGroup;
        $.each(errorsArray, function(index, value){
            formGroup = $(".field-" + formModel_id + "-" + index)[0];
            $(formGroup).addClass('has-error');
            attrInput = $("#" + formModel_id + "-" + index)[0];
            $(attrInput).attr("aria-invalid", "true");
            errorsBlock = $(attrInput).nextAll(".help-block")[0];
            $(errorsBlock).html(value);
        });
    } else {
        console.log(errorsArray)
    }

}

function changeSort(data) {
    console.log(data.value);
   // document.location.href = urlUploadStructure + '/?id=' + selected_id;
    $.ajax({
        url: _BASE_URL + '/main/change-sort',
        type: "POST",
        data: {'sort' : data.value},
        dataType: 'json',
        beforeSend: function() {
            preloader('show', 'mainContainer', 0);
        },
        complete: function(){
            preloader('hide', 'mainContainer', 0);
        },
        success: function(response){
            console.log(response);
            if (response['status']) {
                document.location.href = document.location.href;
            }
        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown);
        }
    })


}


saveHistory();

//saveHistory();

