function updateItems(r) {
    _assigments = r;
    refreshItemsX('avaliable', 'Roles');
    refreshItemsX('assigned', 'Roles');
    refreshItemsX('avaliable', 'Permissions');
    refreshItemsX('assigned', 'Permissions');


}



function refreshItemsX(target, type) {
    var $list = $('select.list[data-target="' + target + type + '"]');
    $list.html('');
    $.each(_assigments[type][target], function () {
        var r = this;
       // console.log(this[0]);
        if (this[0] == '*'){ //-- признак, что разрешение взято из роли
            $('<option>').text(r).val(r).prop('disabled',true).appendTo($list);
        } else {
            $('<option>').text(r).val(r).appendTo($list);
        }
    });
    if (target == 'assigned'){
        $list.children().addClass('assignedItemsList');
    }

}

// initial
//refreshRoles('avaliable');
//refreshRoles('assigned');
$(document).ready ( function(){
    refreshItemsX('avaliable', 'Roles');
    refreshItemsX('assigned', 'Roles');
    refreshItemsX('avaliable', 'Permissions');
    refreshItemsX('assigned', 'Permissions');

    $('.btn-assign').click(function () {
        var $this = $(this);
        var route = '/adminxx/assignment/assign';
        if ($this.hasClass('actionRevoke')){
            route = '/adminxx/assignment/revoke';
        }
        var target = $this.data('target');
        var roles = $('select.list[data-target="' + target + '"]').val();
      //  var route = $this.data('rout');
        var data = {
            type: $this.data('type'),
            user_id: $this.data('user_id'),
            items: roles
        };
        if (roles && roles.length) {
            $this.children('i.glyphicon-refresh-animate').show();
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


});

//console.log(_opts);
