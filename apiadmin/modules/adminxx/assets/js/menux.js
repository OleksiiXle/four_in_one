function clickItemFunction(tree_id, selected_id) {
   // alert('clickItemFunction ' + tree_id + ' ' + selected_id);
    $.ajax({
        url: _BASE_URL + '/adminxx/menux/get-menux',
        type: "GET",
        data: {
            'id' : selected_id
        },
        beforeSend: function() {
             preloader('show', 'mainContainer', 0);
        },
        complete: function(){
            preloader('hide', 'mainContainer', 0);
        },
        success: function(response){
            $("#menuInfo").html(response);
        },
        error: function (jqXHR, error, errorThrown) {
            console.log(error);
            console.log(errorThrown);
            console.log(jqXHR);
         //   errorHandler(jqXHR, error, errorThrown);
        }
    });

}
