const TYPE_IMAGE = '1';
const TYPE_VIDEO = '2';
const TYPE_AUDIO = '3';
const TYPE_TEXT = '4';
const TYPE_LINK = '5';

var files; // переменная. будет содержать данные файлов
var newMediaFileName;
$(document).ready ( function(){

    if (_id != undefined){
        refreshPostMediaList();
    }

    $(".newMediaBtn").click('on', function () {
        switch (this.dataset.type){
            case 'image':
                $("#mediaTitle").html("Новое изображение");
                $("#postmedia-type").val(TYPE_IMAGE);
                $("#imagePreview").show();
                $("#post-media-create > div.form-group.field-postmedia-file_name").hide();
                break;
            case 'link':
                $("#mediaTitle").html("Новая ссылка");
                $("#postmedia-type").val(TYPE_LINK);
                $("#imagePreview").hide();
                $("#post-media-create > div.form-group.field-postmedia-file_name").show();
                $("#post-media-create > div.form-group.field-postmedia-file_name > label").html("Ссылка");

                break;
        }
        $("#newPostMedia").show('slow');
        $("#listPostMediaArea").hide('slow');
    });

    $("#resetMediaBtn").click('on', function () {
        resetNewMedia();
    });

    //--  загрузка и вывод превью
    $('input[type=file]').on('change', function(){
        // ничего не делаем если files пустой
        files = this.files;
        if( typeof files == 'undefined' ) return;
      //  console.log(files);
        // создадим объект данных формы
        var data = new FormData();
        data.append( 0, files[0] );
        data.append( 'type', 'image' );
        console.log(data);
        $.ajax({
            url         : '/post/post/get-media-preview',
            type        : 'POST', // важно!
            data        : data,
            cache       : false,
            dataType    : 'json',
            // отключаем обработку передаваемых данных, пусть передаются как есть
            processData : false,
            // отключаем установку заголовка типа запроса. Так jQuery скажет серверу что это строковой запрос
            contentType : false,
            // функция успешного ответа сервера
            success     : function( response){
              //  console.log(response);
                if (response['status']){
                   // objDump(response['data']);
                    $("#previewImage").attr("src",(response['data']['webFullFileName']));
                    $("#postmedia-file_name").val(response['data']['fileName']);
                } else {
                    objDump(response['data'])
                }
            },
            error: function( jqXHR, status, errorThrown ){
                console.log( 'ОШИБКА AJAX запроса: ' + status, jqXHR );
            }

        });



    });

    //-- сохранение изображения и обновление таблицы медиа
    $('.saveMediaBtn').on( 'click', function( event ){
        event.stopPropagation(); // остановка всех текущих JS событий
        event.preventDefault();  // остановка дефолтного события для текущего элемента - клик для <a> тега

        createPostMedia(this.dataset.type);
    });


});

function refreshPostMediaList() {
    return $.ajax({
        url: '/post/post/get-post-media',
        type: "GET",
        data: {
            'id' : _id
        },
        beforeSend: function() {
            preloader('show', 'mainContainer', 0);
        },
        complete: function(){
            preloader('hide', 'mainContainer', 0);
        },
        success: function(response){
          //  console.log(response);
            $("#listPostMedia").html(response);
        },
        error: function (jqXHR, error, errorThrown) {
            console.log(error);
            console.log(jqXHR);
            errorHandler(jqXHR, error, errorThrown);
        }
    });


}

function createPostMedia(type) {
    var formData = $("#post-media-create").serialize();
    switch (type){
        case 'image':
            break;
        case 'link':
            break;
    }
  //  console.log(formData);

    $.ajax({
        url: '/post/post/create-post-media',
        type: "POST",
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            preloader('show', 'mainContainer', 0);
        },
        complete: function(){
            preloader('hide', 'mainContainer', 0);
        },
        success: function(response){
            console.log(response);
            if (response['status']){
                refreshPostMediaList();
                resetNewMedia(type);
            } else {
                objDump(response['data']);
            }
        },
        error: function (jqXHR, error, errorThrown) {
            console.log(jqXHR);
            errorHandler(jqXHR, error, errorThrown);

        }
    })

}

function deletePostMedia(id) {
    if (confirm('Подтвердите удаление')){
        $.ajax({
            url: '/post/post/delete-post-media',
            type: "POST",
            data: {'postMediaId':id},
            dataType: 'json',
            beforeSend: function() {
                preloader('show', 'mainContainer', 0);
            },
            complete: function(){
                preloader('hide', 'mainContainer', 0);
            },
            success: function(response){
                console.log(response);
                if (response['status']){
                    refreshPostMediaList();
                } else {
                    objDump(response['data']);
                }
            },
            error: function (jqXHR, error, errorThrown) {
                console.log(jqXHR);
                errorHandler(jqXHR, error, errorThrown);

            }
        })

    }
    return false;
}

function resetNewMedia(type) {
    switch (type){
        case 'image':
            $("#previewImage").attr("src",(_cleanImage));
            $('input[type=file]').val(null);
            break;
    }
    $("#postmedia-file_name").val('');
    $("#postmedia-name").val('');
    $("#newPostMedia").hide('slow');
    $("#listPostMediaArea").show('slow');
}



