/*
$(document).ready ( function(){
});
*/
(function ($) {
    $.fn.selectXle = function (selectId, _selectedItem, _selectedText, _clickFunctionBody) {
        var selectedItemId = _selectedItem;
   //     console.log(selectId);
      //  console.log('#topItem_' + selectId);
        var clickFunction = new Function('item', _clickFunctionBody );
        /*
        var styles = JSON.parse(_userStylesJson);
        var s_listItem = styles.listItem;
        var s_itemsArea = styles.itemsArea;
        $.each(s_listItem, function(i, val) {
            $('.listItem_' + selectId).css(i,val + '!important');
            console.log($('.listItem_' + selectId));
          //  console.log(val);
        });
        $.each(s_itemsArea, function(i, val) {
            $('#itemArea_' + _selectedItem).css(i,val+ '!important');
          //  console.log(i);
          //  console.log(val);
        });
        */
        $('#topItem_' + selectId).hover(
            function(){
                $('#items_' + selectId).show(500);
             //   $('#items_' + selectId).fadeIn();
            },
            function(){
                $('#items_' + selectId).hide(500);
            });

        $('.listItem_' + selectId).hover(
            function(){
             //   console.log(this.id);

                $('#itemArea_'  + selectId + '_' + this.id).removeClass('itemArea').addClass('itemAreaActive');
            },
            function(){
                $('#itemArea_'  + selectId + '_' + this.id).removeClass('itemAreaActive').addClass('itemArea');
            });

        $('.listItem_' + selectId).on( 'click', function( event ){
            var that = this;
            event.stopPropagation(); // остановка всех текущих JS событий
            event.preventDefault();  // остановка дефолтного события для текущего элемента - клик для <a> тега
            if (typeof clickFunction == 'function'){
               // console.log(that);
              //  console.log(that.dataset);
                var arr =[];
                arr.push(changeSelectedItem(that));
                arr.push(clickFunction(selectedItemId));
                collector.run(arr);
            }
        });

        function changeSelectedItem(item) {
            if (selectedItemId != item.dataset.id){
                selectedItemId = item.dataset.id;
                var newText = item.innerText;
                //    console.log('old  ' + $('#selectedItem_' + selectId).data('id') + ' - ' + $('#selectedItem_' + selectId).text());
                //    console.log('new  ' + selectedItemId + ' - ' + newText);
              //  $('#itemArea_' + _selectedItem).show();
             //   $('#itemArea_' + _selectedItem).children().show();
                $('#selectedItem_' + selectId).text(newText).data('id', selectedItemId);
              //  $('#selectedItem_' + selectId).data('id', selectedItemId);
                //    console.log('after  ' + $('#selectedItem_' + selectId).data('id') + ' - ' + $('#selectedItem_' + selectId).text());
                //    console.log($('#selectedItem_' + selectId));
                $(item).hide();
            }

        }
    };
})(window.jQuery);


