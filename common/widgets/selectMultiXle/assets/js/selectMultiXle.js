/*
$(document).ready ( function(){
});
*/
(function ($) {
    $.fn.selectMultiXle = function (selectId, textAreaAttributeId, itemsArrayOwn) {
      //  console.log(itemsArrayOwn);
        let key;
        let selectedItems = [];
        let item;
        let ownItems = JSON.parse(itemsArrayOwn);
      //  console.log(ownItems);
      //  console.log(Object.keys(ownItems).length);
        if (Object.keys(ownItems).length > 0) {
            for (key in ownItems) {
                selectedItems.push(key);
            }
            $("#" + textAreaAttributeId).val(JSON.stringify(selectedItems));
        }
      //  console.log(translate('Показать список выбора'));

        //--выбор
        $(".choise-row-" + selectId).on('click', function () {
           // console.log(this.dataset.key);
            this.dataset.choised = '1';
            key = this.dataset.key;
            item = $("#selected_" + selectId + " div[data-key='" + key + "']")[0];
            // console.log(item);
            item.dataset.selected = '1';
            $(this).removeClass('active').addClass('no-active');
            $(item).removeClass('no-active').addClass('active');
            $("#no-selected-message-"+ selectId).removeClass('active').addClass('no-active');
            selectedItems.push(key);
           // console.log(selectedItems);
            $("#" + textAreaAttributeId).val(JSON.stringify(selectedItems));
        });

        //-- отмена выбора
        $(".selection-row-" + selectId).on('click', function () {
           // console.log(this.dataset.key);
            this.dataset.selected = '0';
            key = this.dataset.key;
            item = $("#choise_" + selectId + " div[data-key='" + key + "']")[0];
            // console.log(item);
            item.dataset.choised = '0';
            $(this).removeClass('active').addClass('no-active');
            $(item).removeClass('no-active').addClass('active');
            if ($("#selected_" + selectId + " div[data-selected='1']").length == 0 ){
                $("#no-selected-message-"+ selectId).removeClass('no-active').addClass('active');
            }
            let i = selectedItems.indexOf(key);
            if (i >= 0) {
                selectedItems.splice(i, 1);
            }
         //   console.log(selectedItems);
            $("#" + textAreaAttributeId).val(JSON.stringify(selectedItems));
        });

        $("#show-list-btn-" + selectId).on('click', function () {
            if ($("#choise_" + selectId).is(":hidden")) {
                $("#choise_" + selectId).show("slow");
                $(this).css("color", "#daa520");
                $(this).find('span').removeClass('glyphicon glyphicon-chevron-down')
                    .addClass('glyphicon glyphicon-chevron-up');
                $(this).attr('title', translate('Скрыть список выбора'));
            } else {
                $("#choise_" + selectId).hide("slow");
                $(this).css("color", "#00008b");
                $(this).find('span').removeClass('glyphicon glyphicon-chevron-up')
                    .addClass('glyphicon glyphicon-chevron-down');
                $(this).attr('title', translate('Показать список выбора'));
            }
        })

    };
})(window.jQuery);


