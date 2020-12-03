function changeUserActivity(user_id){
    //  console.log(user_id);
    $.ajax({
        url: _BASE_URL + '/adminxx/user/change-user-activity',
        type: "POST",
        data: {
            'user_id'   : user_id,
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
          // console.log(response);
            if (response['status']){
                var color = (response['data'] == 'active') ? 'red' : 'grey';
                $("#activityIcon_" + user_id).css({ 'color': color});

              //  $.pjax.reload({container:"#gridUsers"});
            } else {
                objDump(response['data'])
            }

        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown);        }
    })

}

function showTabs(user_id) {
   //alert(user_id);
  //  $("#uldropDown" + user_id).show();
    $(".submenu" + user_id).show(500);
}

function hideTabs(user_id) {
   //alert(user_id);
  //  $("#uldropDown" + user_id).show();
    $(".submenu" + user_id).hide(500);
}

$('.menu-item').hover(
    function(){
        $(this).children('ul').show(500);
        /*
        $(this).children('ul').each(function () {
            $(this).removeClass('childrenNoActive').addClass('childrenActive');
        });
        */
    },
    function(){
        // $(this).find('ul').hide(500);
        /*
        $(this).find('ul').each(function () {
            $(this).removeClass('childrenActive').addClass('childrenNoActive');
        });
        */


    });

$('.menu-tops').hover(
    function(){
        // $(this).children('ul').show(500);
        /*
        $(this).children('ul').each(function () {
            $(this).removeClass('childrenNoActive').addClass('childrenActive');
        });
        */
    },
    function(){
        $(this).find('ul').hide(500);
        /*
        $(this).find('ul').each(function () {
            $(this).removeClass('childrenActive').addClass('childrenNoActive');
        });
        */


    });

