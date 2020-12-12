/*
Приходит из вьюхи:
const GRID_ID = '$this->id';
const GRID_NAME = '$this->name';
const USE_AJAX = 1 / 0
const USE_CUSTOM_UPLOAD_FUNCTION = '$this->useCustomUploadFunction';
var _gridModel = '". addcslashes(static::class, '\\') . "'';";
var _filterClassShortName = '" . $this->dataProvider->filterClassShortName . "';
var _filterModel = '......'
var _workerClass = '......'
var _checkedIdsFromRequest = ". json_encode($this->dataProvider->filterModel->checkedIds) . ";";
*/

var filterQuery = [];
var filterQueryJSON = '{}';
var filterQueryObject = {};
var checkedIds = [];

$(document).ready(function(){
    checkedIds = _checkedIdsFromRequest;
   console.log(_gridModel);
//    console.log(checkedIds);
    if (USE_AJAX) {
      //  console.log($(PJAX_CONTAINER_ID + ' a'));
      //  console.log('.gridLink_' + GRID_ID);
     //   console.log($('.gridLink_' + GRID_ID));
        $(document).on('click', '.gridLink_' + GRID_ID, function(event) {
            event.preventDefault();
            event.stopPropagation();
            doAjax(this.href, 'reload');
        });
    }
});


//-- обработать href с учетом фильтра, пагинации и сортировки и сделать pjax с обработанным href
function doAjax(href, action) {
   // console.log(checkedIds);
    var hr = getHrefWithFilter(href);
    switch (action) {
        case 'checkAll':
        case 'unCheckAll':
            checkedIds = [];
            break;
    }
  //  console.log(hr);
    $.ajax({
        url: hr,
        type: "POST",
        data: {
                'gridName' : GRID_NAME,
                'checkedIds' : JSON.stringify(checkedIds),
                'action' : (action != "undefined") ? action : 'none'
        },
        dataType: 'json',
        beforeSend: function() {
           // preloader('show', 'mainContainer', 0);
        },
        complete: function(){
           // preloader('hide', 'mainContainer', 0);
        },
        success: function(response){
         //   console.log(action);
            $("#" + GRID_ID).html(response['body']);
            history.pushState({}, '', hr);
            if (action == 'reload') {
                checkedIds = response['checkedIds'];
            }
            switch (action) {
                case 'checkAll':
                    break;
                case 'unCheckAll':
                    break;
                case 'uploadChecked':
                    startBackgroundUploadTask();
                    break;
            }
//   console.log(checkedIds);

        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown)            }
    });
}

//-- запрос на doPjax по кнопке "Применить фильтр"
function useFilter() {
  //  checkedIds = [];
 //   console.log(checkedIds);
    doAjax(window.location.href, 'reload');
}

//-- обновить фильтр, взять пагинацию и сортировку из href, и на их основании сформировать новый href
function getHrefWithFilter(href) {
   // console.log('**************************');
    getFilterQuery();
    var url = parseUrl(href);
  //  console.log(url.path);
  //  console.log(url.params);
    var newHref = url.path;
    if (filterQuery.length > 0) {
        url.params['filter'] = filterQueryJSON;
    }
 //   console.log(url.params);
    var first = true;
    for (var key in url.params) {
        if (first) {
            first = false;
            newHref += '?' + key + '=' + url.params[key];
        } else {
            newHref += '&' + key + '=' + url.params[key];
        }
    }

 //   console.log(href);
 //   console.log(newHref);
 //   console.log('**************************');

    return encodeURI(newHref);
}

//-- обновить filterQuery, filterQueryJSON на основании данных формы фильтра
function getFilterQuery() {
    filterQuery = [];
    filterQueryJSON = '{}';
  //  console.log(checkedIds);

    /*
    if (checkedIds.length > 0) {
        $('#' + _filterClassShortName.toLowerCase() + '-checkedidsjson').val(JSON.stringify(checkedIds));
    }
    */

    var bufName;
    $('[id^=' + _filterClassShortName.toLowerCase() + '-]').each(function(index, value) {
        if (value.value.length > 0) {
            bufName = value.name.replace(_filterClassShortName, '').replace('[', '').replace(']', '');
            switch (value.type) {
                case 'hidden':
                case 'text':
                    filterQuery.push({'name' : bufName, 'value' : value.value });
                    filterQueryObject[bufName] = value.value;
                    break;
                case 'checkbox':
                    if (value.checked) {
                        filterQuery.push({'name' : bufName, 'value' : 1 });
                        filterQueryObject[bufName] = 1;
                    }
                    break;
            }
        }
    });
    $('select[id^=' + _filterClassShortName.toLowerCase() + '-]').each(function(index, value) {
        if (value.value != 0) {
            bufName = value.name.replace(_filterClassShortName, '').replace('[', '').replace(']', '');
            filterQuery.push({'name' : bufName, 'value' : value.value });
            filterQueryObject[bufName] = value.value;
        }
    });
    $('textarea[id^=' + _filterClassShortName.toLowerCase() + '-]').each(function(index, value) {
        if (value.value != 0) {
            bufName = value.name.replace(_filterClassShortName, '').replace('[', '').replace(']', '');
            filterQuery.push({'name' : bufName, 'value' : value.value });
            filterQueryObject[bufName] = value.value;
        }
    });
    filterQueryJSON = JSON.stringify(filterQuery);
}

//-- модификация checkedIds по нажатию на чекбокс (+ или -)
function checkRow(checkbox){
   // console.log(checkedIds);
    var id = parseInt($(checkbox)[0].dataset['id']);
    var ind = checkedIds.indexOf(id);
    if (checkbox.checked) {
       // console.log(id);
        if (ind <= 0) {
            checkedIds.push(id);
        }
    } else {
        if (ind > 0) {
            checkedIds.splice(ind, 1);
        }
    }
   // console.log(checkedIds);
}

//-- показать/скрыть форму фильтра на гриде
function buttonFilterShow(button) {
    if ($("#filterZone").is(":hidden")) {
        $("#filterZone").show("slow");
        $(button).css("color", "#daa520");
        $(button)[0].innerHTML = '<span class="glyphicon glyphicon-chevron-up"></span>';
        if (typeof clickButtonFilterShowFunction == 'function'){
            clickButtonFilterShowFunction();
        }
    } else {
        $("#filterZone").hide("slow");
        $(button).css("color", "#00008b");
        $(button)[0].innerHTML = '<span class="glyphicon glyphicon-chevron-down"></span>';
        if (typeof clickButtonFilterHideFunction == 'function'){
            clickButtonFilterHideFunction();
        }

    }
}

//-- очистить форму фильтра и перелоадить грид через pjax
function cleanFilter(reload){
  //  console.log(parseUrl());
  //  console.log(window.location);
 //   console.log(window.location.origin +  window.location.pathname);
    $('input[type="text"][ id^=' + _filterClassShortName.toLowerCase() + '-]').val('');
  //  $('textarea').val('');
    $('textarea[id^=' + _filterClassShortName.toLowerCase() + '-]').val('');
 //   $('textarea[id^=' + _filterClassShortName.toLowerCase() + '-]').innerHTML('');
    $('input[type="checkbox"][id^=' + _filterClassShortName.toLowerCase() + '-]').prop('checked', false);
    $('select[id^=' + _filterClassShortName.toLowerCase() + '-]').val(0);
    $("#" + _filterClassShortName.toLowerCase() + "-allrowsarechecked").val(0);
    checkedIds = [];
//    console.log(checkedIds);
    history.pushState({}, '', window.location.origin +  window.location.pathname);
    if (reload) {
        useFilter();
    }
   // console.log($('textarea[id^=' + _filterClassShortName.toLowerCase() + '-]'));
}

//-- распарсить href на path и params
function parseUrl(href) {
//    console.log(window.location);
   // console.log('**** parseUrl ');
  //  console.log(href);
    var paramsStr = '';
    var res = {
        path : window.location.origin +  window.location.pathname,
        params : {}
    };
    if (href == undefined) {
     //   console.log('href == undefined');
        paramsStr = window.location.search;
    } else {
   //     console.log('href != undefined');
       // console.log(href);
        var startParams = href.indexOf('?');
        if (startParams > 0) {
            paramsStr = href.substr(startParams);
        }
    }
 //   console.log('paramsStr = ' + paramsStr);
    if (paramsStr !== '') {
        res.params = paramsStr.replace('?','').split('&').reduce(
            function(p,e){
                var a = e.split('=');
                p[ decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
                return p;
            },
            {}
        );
    }
  //  console.log(res);
  //  console.log('**** parseUrl ');

    return res;
}

//-- запуск выгрузки файла со списком в режиме фоновой задачи
function startBackgroundUploadTask() {
 //   getFilterQuery();
    filterQuery.push({'name':'checkedIds', 'value' : JSON.stringify(checkedIds)});
    console.log(filterQuery);
    var params = {
        'mode' : 'dev',
        'useSession' : true,
       // 'mode' : 'dev',
        'checkProgressInterval' : 500,
        'showProgressArea' : true,
        'windowMode' : 'popup',
        'title' : 'Подготовка файла',
        'widht' : 500,
        'doneScript' : 'downloadFile(this);',
        'model' : _workerClass,
        'arguments' : {
            'filterModel' : _filterModel,
            'gridModel' : _gridModel,
            'query' : filterQuery,
            'uploadFunction' : (USE_CUSTOM_UPLOAD_FUNCTION) ? 'custom' : 'default'
        },
        'showErrorsArea' : true,
        'doOnSuccessTxt' : "$(this.doneButon).show();",
        /*
            'doOnSuccessTxt' : "this.cleanAreas();" +
                                "this.uploadResult(true, true, 'result');",
                                */
    };
    console.log(filterQuery);

    startNewBackgroundTask(params);
}

function actionWithChecked(action) {
 //   console.log(action.value);
    doAjax(window.location.href, action.value);

    //   console.log(checkedIds);
}


//--@deprecated
function getGridFilterData(modelName, formId, urlName, container_id) {
    //   alert(modelName + ' ' + formId + ' ' + urlName + ' ' + container_id);
    var filterData = $("#" + formId).serialize();
    //  objDump(data);
    $.ajax({
        url: urlName ,
        type: "POST",
        data:  filterData,
        timeout: 3000,
        success: function(response){
            objDump(response);
        },
        error: function (jqXHR, error, errorThrown) {
            alert( "Ошибка фильтра : " + modelName + " " + error + " " +  errorThrown);
        }

    });



}

//--@deprecated
function checkOnlyChecked(item) {
    if ($(item).prop('checked')) {
        $('input[type="text"][ id^=' + _filterClassShortName.toLowerCase() + '-]').val('');
        $('input[type="checkbox"][id^=' + _filterClassShortName.toLowerCase() + '-][id!=' + _filterClassShortName.toLowerCase() + '-showonlychecked]')
            .prop('checked', false);
        $('select[id^=' + _filterClassShortName.toLowerCase() + '-]').val(0);
        history.pushState({}, '', window.location.origin +  window.location.pathname);
    }

}

function getHrefWithFilter__COPY(href) {
    var hr = href;
    var filterFragment = '';
    var hasFilter = false;
    var filterStart = hr.indexOf('&filter=1');
    var filterEnd;
    if (filterStart > 0) {
        hasFilter = true;
    } else {
        filterStart = hr.indexOf('?filter=1');
        if (filterStart > 0) {
            hasFilter = true;
        }
    }
    if (hasFilter) {
        filterEnd = hr.indexOf('&filterEnd=1');
        filterFragment = hr.substring(filterStart, filterEnd + 12);
        console.log(hr);
        console.log(filterFragment);
        hr = hr.substr(0, filterStart) + hr.substr(filterEnd + 12, hr.length);
    }
    if (filterQuery.length > 0){
        if (hr.indexOf('?') < 0) {
            hr += '?filter=1';
        } else {
            hr += '&filter=1';
        }
        $(filterQuery).each(function (index, value) {
            hr += '&' + value['name'] + '=' + value['value'];
        });
        hr += '&filterEnd=1';

    }

    return hr;
}







