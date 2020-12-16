$(document).ready(function () {
    checkFlashMessages();
});

function showMenu() {
    $('#xCover, #xMenu, #xMenuContent').fadeIn();
}

function hideMenu() {
    $('#xCover, #xMenu, #xMenuContent').fadeOut();
}

function menuClick() {
    $('#xWrapper').on('click', function(event) {
        var select = $('#xMenuContent');
        if ($(event.target).closest(select).length)
            return;
        $('#xCover, #xMenu, #xMenuContent').fadeOut();
        $('#xWrapper').unbind('click');
    });
}

function showModal(pixWidht, pixHeight, headerHtml, content ) {
    //Получаем ширину и высоту окна
    var winH = $(window).height();
    var winW = $(window).width();
    var xModal = $("#xModalWindow");
    //Устанавливаем всплывающее окно по центру
    $(xModal).css('width', pixWidht);
    $(xModal).css('height', pixHeight);
    $(xModal).css('top', winH/2-xModal.height()/2);
    $(xModal).css('left', winW/2-xModal.width()/2);
    $('#xModalHeader').html(headerHtml);
    //эффект перехода
    $('#xCover, #xModal, #xModalWindow').fadeIn();
    if (typeof content !== 'undefined'){
        $('#xModalContent').html(content);
    }
}

function hideModal() {
    $('#xModalHeader, #xModalContent').html('');
    $('#xCover, #xModal, #xModalWindow').fadeOut();
}

//-- показать/убрать прелоадер, parent- ид элемента после которого рисуется прелоадер, и который будет затухать
//-- id -порядковый номер прелоадера - чтобы не былдо конфликтов
function preloader(mode, parent, id) {
   // console.log(mode);
    var parentDiv = $("#" + parent);
    var preloader_id = 'preloaderXle' + id;
    switch (mode) {
        case 'show':
            parentDiv.append('<div id="' + preloader_id + '" class="loaderXle LockOff"></div>'
                + '<div id="loaderText" class="LockOff"></div>');
            parentDiv.removeClass('LockOff').addClass('LockOn');
            break;
        case 'hide':
            $("#" + preloader_id).remove();
            $("#loaderText").remove();
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

//-- обработка ошибок после аякс запроса
//-- если 403 - в #flashMessage /views/layouts/commonLayout выводится соответствующее сообщение
function errorHandler(jqXHR, error, errorThrown){
    console.log('Помилка:');
    console.log(error);
    console.log(errorThrown);
    console.log(jqXHR);
    console.log(jqXHR.responseText);
    if (jqXHR['status']==403){
        //   alert('accessDeny');
        var flashMessage = '';
        flashMessage += '<div class="alert alert-danger alert-dismissible">' + 'Дія заборонена' +'</div>';
        $("#flashMessage").show('slow');
        $("#flashMessage").html(flashMessage);
        setTimeout(function() {
            $("#flashMessage").hide('slow');
        }, 5000);
        $("#main-modal-lg").hide;
        $("#main-modal-md").hide;
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
        }, 6000);
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

function checkFlashMessages() {
    if (typeof(_fms) != 'undefined' ){
     //   console.log(_fms);
        var typeMess = '';
        var messageTxt = '';
        $.each(_fms, function (type, message) {
            switch (type) {
                case 'success':
                    typeMess = 'class = "alert-success"';
                    break;
                case 'warning':
                    typeMess = 'class = "alert-danger"';
                    break;
            }
            if (typeof(message) == 'object'){
                $.each(message, function (index, txt){
                    messageTxt += '<div ' + typeMess + '>' + txt +'</div>';
                });
            } else {
                messageTxt += '<div ' + typeMess + '>' + message +'</div>';
            }
        });
        $("#flashMessage").html(messageTxt);
        $("#flashMessage").show();

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

//*******************************************************************************


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





