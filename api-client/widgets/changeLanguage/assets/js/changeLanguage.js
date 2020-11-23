/*
    var _languagesList = <?$languagesList?>;
    var _selectedLanguage = '<?$selectedLanguage?>';
    var _changeLanguageRoute = '<?$changeLanguageRoute?>'';
 */

var selectedLanguage;
var languagesDropdown;
var itemTop, itemLeft, itemHeight;


$(document).ready ( function(){
    $('body').append(getLanguagesDropdown());
    selectedLanguage = $("#selectedLanguage");
    languagesDropdown = $("#languagesDropdown");
    setDropdownPosition();
});

function setDropdownPosition(){
    itemTop = $(selectedLanguage).offset().top;
    itemLeft = $(selectedLanguage).offset().left;
    itemHeight = $(selectedLanguage).css('height');

    $(languagesDropdown).css('top', (itemTop + 10))
        .css('left', (itemLeft + 10));
}

function getLanguagesDropdown(){
    let html = "<div id='languagesDropdown' class='languagesDropdown' style='display: none'>";
    for (langKey in _languagesList){
        if (langKey != _selectedLanguage) {
            html += "<a class='languagesForSelect' href='" + _changeLanguageRoute + "?language=" + langKey + "'>  "
                   + _languagesList[langKey]
                   + "</a><br>";
        }
    }
    html += "</div>";
    return html;
}

$(window).resize(function(){
    setDropdownPosition()
});

$("#selectedLanguage").hover(
    function(){
        $('#languagesDropdown').show(500);
        $(this).hide();
    },
    function(){
    });

$(document).on("mouseenter", "#languagesDropdown", function() {
});

$(document).on("mouseleave", "#languagesDropdown", function() {
    $('#selectedLanguage').show(500);
    $(this).hide();
});
