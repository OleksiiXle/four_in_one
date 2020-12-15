
function updateItems(r) {
    _assigments = r;
    refreshItemsX();
}


$('.btn-assign').click(function () {
    //  console.log(this);
    var $this = $(this);
    var target = $this.data('target');
    var items = $('select.list[data-target="' + target + '"]').val();
    var route = _BASE_URL + '/adminxx/auth-item/assign';
    if ($this.hasClass('actionRevoke')){
        route = _BASE_URL + '/adminxx/auth-item/revoke';
    }

    //   var route = $this.data('rout');
    var data = {
        name: _name,
        type: _type,
        items: items
    };
 //  console.log(data);
 //  return;

    if (items && items.length) {
        $.ajax({
            url: route,
            type: "POST",
            data: data,
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
                    updateItems(response['data']);
                } else {
                    objDump(response['data']);
                }
            },
            error: function (jqXHR, error, errorThrown) {
                errorHandler(jqXHR, error, errorThrown)            }
        });
    }
    return false;
});

function refreshItemsX() {
    var $list = $('select.list[data-target="avaliable"]');
    $list.html('');
    var groups = {
        role: [$('<optgroup label="Роли" class="roles">'), false],
        permission: [$('<optgroup label="Разрешения">'), false],
        route: [$('<optgroup label="Маршруты">'), false],
    };
    $.each(_assigments['avaliable']['Roles'], function (key, name) {
        if (_name !== name){
            $('<option>').text(name).val(name).appendTo(groups['role'][0]);
            groups['role'][1] = true;
        }
    });
    $.each(_assigments['avaliable']['Permissions'], function (key, name) {
        if (_name !== name){
            $('<option>').text(name).val(name).appendTo(groups['permission'][0]);
            groups['permission'][1] = true;
        }
    });
    $.each(_assigments['avaliable']['Routes'], function (key, name) {
        $('<option>').text(name).val(name).appendTo(groups['route'][0]);
        groups['route'][1] = true;
    });
    $.each(groups, function () {
        if (this[1]) {
            $list.append(this[0]);
        }
    });

    $list = $('select.list[data-target="assigned"]');
    $list.html('');
    var groups = {
        role: [$('<optgroup label="Роли">'), false],
        permission: [$('<optgroup label="Разрешения">'), false],
        route: [$('<optgroup label="Маршруты">'), false],
    };
    $.each(_assigments['assigned']['Roles'], function (key, name) {
        if (_name !== name){
            $('<option>').text(name).val(name).appendTo(groups['role'][0]);
            groups['role'][1] = true;
        }
    });
    $.each(_assigments['assigned']['Permissions'], function (key, name) {
        if (_name !== name){
            $('<option>').text(name).val(name).appendTo(groups['permission'][0]);
            groups['permission'][1] = true;
        }
    });
    $.each(_assigments['assigned']['Routes'], function (key, name) {
        $('<option>').text(name).val(name).appendTo(groups['route'][0]);
        groups['route'][1] = true;
    });
    $.each(groups, function () {
        if (this[1]) {
            $list.append(this[0]);
        }
    });


}


// initial
//console.log(_assigments);
refreshItemsX();



function linkClick(item) {
    //console.log(item);

    $("tr").removeClass('activeGridRow');
     $(item).closest('tr').addClass('activeGridRow');

}




