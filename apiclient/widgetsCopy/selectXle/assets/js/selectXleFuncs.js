/*
$(document).ready ( function(){
});
*/

$('#topItem').hover(
    function(){
        $('#items').show(500);
    },
    function(){
        $('#items').hide(500);
    });

$('.listItem').hover(
    function(){
        $('#itemArea_' + this.id).removeClass('itemArea').addClass('itemAreaActive');
    },
    function(){
        $('#itemArea_' + this.id).removeClass('itemAreaActive').addClass('itemArea');
    });

$('.btnItem').on( 'click', function( event ){
    event.stopPropagation(); // остановка всех текущих JS событий
    event.preventDefault();  // остановка дефолтного события для текущего элемента - клик для <a> тега
    if (typeof clickFunction == 'function'){
        var arr =[];
        arr.push(clickFunction(this.id));
        arr.push(changeSelectedItem());
        collector.run(arr);
    }
});

function changeSelectedItem() {
    $('#itemArea_' + _selectedItem).show();
    $('#itemArea_' + _selectedItem).children().show();
    _selectedItem = this.id;
    _selectedText = this.innerText;
    $('#selectedItem').html(_selectedText);
    $(this).hide();

}
