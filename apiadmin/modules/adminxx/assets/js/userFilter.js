var exportQuery;
var limit, offset;
var reportFileName;

$(document).ready(function () {
    getExportQuery();
  //  selected_id = $(depId)[0].value;
 //   selectedIdChange(selected_id, item_class);
   // choseDepartment();
    /*
    var depId = $("#userfilter-treedepartment_id");
    department_id = $(depId)[0].value;
    selected_id=department_id;
    selectedIdChange(selected_id, item_class);
    */
    //initDep();



});

//-- нажатие на подразделение
function clickItemFunction(id, type) {

    var depId = $("#userfilter-treedepartment_id") ;
    var depName = $("#userfilter-treedepartmentname") ;
    $.ajax({
        url: _BASE_URL + '/adminxx/user/get-department-full-name',
        type: "POST",
        dataType: 'json',
        data: {
            'department_id' : id
        },
        success: function(response){
          //  console.log(response);
            if (depName.length > 0){
                $(depName)[0].value = response['data'];
            }

            if (response['status']){
                if (depId.length > 0){
                    $(depId)[0].value = id;
                }
            }
        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown);
        }
    });
}

//-- нажатие на подразделение
function initDep() {
    var depId = $("#userfilter-treedepartment_id").val();
    console.log(depId);
    if (depId != 0){
        var depName = $("#userfilter-treedepartmentname") ;
        $.ajax({
            url: _BASE_URL + '/adminxx/user/get-department-full-name',
            type: "POST",
            dataType: 'json',
            data: {
                'department_id' : depId
            },
            success: function(response){
                console.log(response['data']);
                $("#userfilter-treedepartmentname").attr('value', response['data']);
                console.log($("#userfilter-treedepartmentname").attr('value'));
            },
            error: function (jqXHR, error, errorThrown) {
                errorHandler(jqXHR, error, errorThrown);
            }
        });
    }

}



function selectedIdChangeFunction(new_id, type) {
 //   var depId = $("#userfilter-treedepartment_id");
 //   department_id = $(depId)[0].value;
 //   choseDepartment();

}


function getExportQuery() {
    var filter = {};
    var sort = {};
    $.each(_exportQuery['filter'], function (index, item) {
        filter[index] = item;
    });
    $.each(_exportQuery['sort'], function (index, item) {
        sort[index] = item;

    });
    exportQuery = {
        'filterModelClass' : _exportQuery['filterModelClass'],
        'filter' : JSON.stringify(filter),
        'sort' : JSON.stringify(sort)
    };

}

function uploadData() {
    $.ajax({
        url: _BASE_URL + '/adminxx/user/export-to-exel-prepare',
        type: "POST",
        data: {
            'exportQuery'   : exportQuery,
            '_csrf' : $('#_csrf').val()

        },
        dataType: 'json',
        beforeSend: function() {
            preloader('show', 'mainContainer', 0);
        },
        complete: function(){
            preloader('hide', 'mainContainer', 0);
        },
        success: function (response) {
            console.log(response);
            if (response['status']){
                var test =  '/adminxx/user/upload-report/?fileName=' + response['data'];
                 alert(test);
                document.location.href = '/adminxx/user/upload-report';
            } else {
                alert ('Помилка підготовки до вивантаження файлу');
                console.log(response);

            }
        },
        error: function (jqXHR, error, errorThrown) {
            alert ('Помилка підготовки до вивантаження файлу');

            errorHandler(jqXHR, error, errorThrown);        }
    })


}

//-- +++ АЯКС Вывод в EXEL данных из таблицы пользователей с учетом фильтра
function uploadDataPartitional() {
    var usersCount;
    $.ajax({
        url: _BASE_URL + '/adminxx/user/export-to-exel-count',
        type: "POST",
        data: {
            'exportQuery'   : exportQuery,
            '_csrf' : $('#_csrf').val()
        },
        dataType: 'json',
        beforeSend: function() {
            preloader('show', 'mainContainer', 0);
        },
        complete: function(){
          //  preloader('hide', 'mainContainer', 0);
        },
        success: function (response) {
          //  console.log(response);
            if (response['status']){
                usersCount = parseInt(response['data']);
                 var usersTotalCount = parseInt(response['data']);
               // alert(usersCount);
                limit = 300;
                offset = -limit;
                var promise = $.when();
                while (usersCount > 0){
                    promise = promise.then(function(){
                        offset +=limit;
                        var todo = ((offset + limit) < usersTotalCount) ? (offset + limit) :  (usersTotalCount);
                        $("#loaderText").html('Готуються записи з ' + (offset +1) + ' по ' + todo + ' із ' + usersTotalCount);

                        return getPartition(limit, offset);
                    }).then(function(response){
                        if (response['status']){
      //                      console.log(response['data']);
                        } else {
                            console.log(response['data']);
                            preloader('hide', 'mainContainer', 0);
                            return $.Deferred();
                        }
                    }).fail(function(){
                        console.log('fail offset=' + offset);
                    });
                    usersCount -= limit;
                }
                promise.then(function(){
                    console.log('OK');
                    var test =  '/adminxx/user/upload-report/?fileName=' + reportFileName;
                  //  alert(test);
                    preloader('hide', 'mainContainer', 0);
                    document.location.href = '/adminxx/user/upload-report';
                });



            } else {
                objDump(response['data']) ;
            }
        },
        error: function (jqXHR, error, errorThrown) {
            alert ('Помилка підготовки до вивантаження файлу');
            errorHandler(jqXHR, error, errorThrown);
            preloader('hide', 'mainContainer', 0);
        }
    })


}

function getPartition() {
    return     $.ajax({
        url: _BASE_URL + '/adminxx/user/export-to-exel-get-partition',
        type: "POST",
        data: {
            'limit' : limit,
            'offset' : offset,
            'exportQuery'   : exportQuery,
            '_csrf' : $('#_csrf').val()
        },
        dataType: 'json',
        success: function (response) {
           // console.log(response);
          //  console.log(limit + '->' + offset);
            if (response['status']){
                reportFileName =  '/adminxx/user/upload-report/?fileName=' + response['data'];
            } else {
                console.log ('Помилка підготовки до вивантаження файлу');
                console.log(response);

            }


        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown);
            preloader('hide', 'mainContainer', 0);
        }
    })

}




