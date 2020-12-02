var _csrf = $('meta[name="csrf-token"]').attr("content");

function actionWithCheckedTranslations(action) {
    console.log('action =' + action.value);
    console.log(checkedIds);
    $.ajax({
        url: '/adminxx/translation/delete-translations',
        type: "POST",
        data:  {
            '_csrf' : _csrf,
            'checkedIds' : checkedIds
        },
        beforeSend: function() {
            preloader('show', 'mainContainer', 0);
        },
        complete: function(){
            preloader('hide', 'mainContainer', 0);
        },
        success: function(response){
            if (response['status']) {
                alert('Удалено ' + response['data'] + ' переводов');
                $.pjax.reload({container:"#translations-grid-container"});
            } else {
                objDump(response);
            }
        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown);
        }

    });

    action.value = 'label';
}