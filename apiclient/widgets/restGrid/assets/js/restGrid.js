var filterQuery = [];
var filterQueryObject = {};
var filterQueryJSON;



$(document).ready(function(){
});

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

//-- обновить filterQuery, filterQueryJSON на основании данных формы фильтра
function getFilterQuery() {
    filterQuery = [];
    filterQueryJSON = '{}';
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

//-- запрос по кнопке "Применить фильтр"
function useFilter() {
    var hr = getHrefWithFilter(window.location.href);

 //   getFilterQuery();
   // console.log(filterQuery);
   // console.log(filterQueryObject);
  //  console.log(filterQueryJSON);
  //  console.log(hr);
    document.location.href = hr;
    //doAjax(window.location.href, 'reload');
}

//-- очистить форму фильтра и перелоадить грид через pjax
function cleanFilter(reload){
    $('input[type="hidden"][ id^=' + _filterClassShortName.toLowerCase() + '-]').val('');
    $('input[type="text"][ id^=' + _filterClassShortName.toLowerCase() + '-]').val('');
    $('textarea[id^=' + _filterClassShortName.toLowerCase() + '-]').val('');
    $('input[type="checkbox"][id^=' + _filterClassShortName.toLowerCase() + '-]').prop('checked', false);
    $('select[id^=' + _filterClassShortName.toLowerCase() + '-]').val(0);
    $("#" + _filterClassShortName.toLowerCase() + "-allrowsarechecked").val(0);
    history.pushState({}, '', window.location.origin +  window.location.pathname);
    useFilter();
}

//-- обновить фильтр, взять пагинацию и сортировку из href, и на их основании сформировать новый href
function getHrefWithFilter(href) {
    // console.log('**************************');
    getFilterQuery();
    var url = parseUrl(href);
      console.log(url.path);
      console.log(url.params);
    var newHref = url.path;
    if (filterQuery.length > 0) {
        url.params['filter'] = filterQueryJSON;
    }
       console.log(url.params);
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







