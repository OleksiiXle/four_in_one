function drawMenu(item) {
    if ($(item).siblings('.items').css('display') == 'none') {
        $('.items').hide();
        $(item).siblings('.items').show();
    } else {
        $(item).siblings('.items').hide();

    }
}