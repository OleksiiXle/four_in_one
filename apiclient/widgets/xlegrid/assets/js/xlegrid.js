/*
Приходит из вьюхи:
var _pjaxClientOptions = {
    container: "#users-grid-container"
    fragment: "#users-grid-container"
    push: true
    replace: false
    scrollTo: false
    timeout: 1000
    }
const USE_PJAX = 1 / 0
const PJAX_CONTAINER_ID = '#users-grid-container'
 var _filterClassShortName = '......'
 var _filterModel = '......'
 var _workerClass = '......'
 */

var filterQuery = [];
var filterQueryJSON = '{}';
var filterQueryObject = {};
var checkedIds = [];

$(document).ready(function(){
   // console.log(PJAX_CONTAINER_ID);
  //  console.log(USE_PJAX);
   // console.log(_pjaxClientOptions);
    //console.log(PJAX_CONTAINER_ID + ' a');
  //  console.log($(PJAX_CONTAINER_ID + ' a'));
   // console.log($("#users-grid-container a [class = 'no-pjax']"));
   // console.log($("#users-grid-container a"));
   // console.log($('a[class!="route no-pjax"]'));
  //  console.log($("a[class!='no-pjax']"));
   // console.log($(PJAX_CONTAINER_ID + ' a [class = "no-pjax"]'));



    if (USE_PJAX) {
      //  console.log($(PJAX_CONTAINER_ID + ' a'));
        $(document).on('click', PJAX_CONTAINER_ID + ' a', function(event) {
            if (!$(this).hasClass('no-pjax')){
                event.preventDefault();
                event.stopPropagation();
                doPjax(this.href);
            }
        });
       // $('#' + _filterClassShortName.toLowerCase() + '-showonlychecked').prop('checked', false);
       // checkedIds = JSON.parse($('#' + _filterClassShortName.toLowerCase() + '-checkedidsjson').val());
     //   console.log(checkedIds);
        /*
        $(PJAX_CONTAINER_ID).on("pjax:beforeSend", function (e, xhr, settings) {
              console.log(e);
              console.log(xhr);
              console.log(settings);
        });
        */

      //  getFilterQuery();
      //  useFilter();
    }
});

//-- обработать href с учетом фильтра, пагинации и сортировки и сделать pjax с обработанным href
function doPjax(href) {
    var hr = getHrefWithFilter(href);
    $.pjax({
        type: 'POST',
        url: hr,
        container: PJAX_CONTAINER_ID,
        fragment: PJAX_CONTAINER_ID,
      //  data: {'query' : filterQuery},
        data: {'checkedIds' : checkedIds},
    })
        /*
        .done(function (response) {
        console.log('done');
        console.log(response);
    })
    */
}

//-- запрос на doPjax по кнопке "Применить фильтр"
function useFilter() {
  //  checkedIds = [];
 //   console.log(checkedIds);
    doPjax(window.location.href);
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
    if (checkedIds.length > 0) {
      //  filterQuery.push({'name' : 'checkedIdsJSON', 'value' : JSON.stringify(checkedIds) });
        //#userfilter-checkedidsjson
        $('#' + _filterClassShortName.toLowerCase() + '-checkedidsjson').val(JSON.stringify(checkedIds));
    }

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
    /*
    if (checkedIds.length > 0) {
        filterQuery.push({'name' : 'checkedIdsJSON', 'value' : JSON.stringify(checkedIds) });
    }
    */
    filterQueryJSON = JSON.stringify(filterQuery);
  //  console.log(filterQueryObject);
   // console.log(filterQuery);
   // console.log(filterQueryJSON);
}

//-- модификация checkedIds по нажатию на чекбокс (+ или -)
function checkRow(checkbox){
   // console.log(checkedIds);
    var id = parseInt($(checkbox)[0].dataset['id']);
    if (checkbox.checked) {
       // console.log(id);
        checkedIds.push(id);
    } else {
        var ind = checkedIds.indexOf(id);
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
    checkedIds = [];
    history.pushState({}, '', window.location.origin +  window.location.pathname);
    if (reload) {
        useFilter();
    }
    console.log($('textarea[id^=' + _filterClassShortName.toLowerCase() + '-]'));
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
    var params = {
        'mode' : 'prod',
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
            'query' : filterQuery,
            'checkedIds' : checkedIds
        },
        'showErrorsArea' : true,
        'doOnSuccessTxt' : "$(this.doneButon).show();",
        /*
            'doOnSuccessTxt' : "this.cleanAreas();" +
                                "this.uploadResult(true, true, 'result');",
                                */
    };

    startNewBackgroundTask(params);
}

function actionWithChecked(action) {
    console.log(action.value);
    console.log(checkedIds);
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







