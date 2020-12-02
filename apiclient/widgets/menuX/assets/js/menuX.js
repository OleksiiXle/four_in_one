//*****************************************
$(document).ready ( function(){
  //  console.log($(".ulMenuX"));
    /*
    $(".ulMenuX").each(function (index, el){
        if ($(el).parents('ul').length > 1){
            $(el).hide();
        }
    });
    */

});

//--действия по клику
function clickAction(item) {
    ///  console.log(icon);
    var ul = $(item).siblings('ul');
    //   console.log(ul);
    if (ul.css('display') == 'block'){
        ul.hide();
    } else {
        ul.show();
    }
}
