var date_format;
var datetime_format;
var daterangepicker_default_config = {};
var daterangepicker_default_ranges = {};
var daterangepicker_locale_config = '';
var daterangepicker_datetime_locale_config = '';
var daterangepicker_single_default_config = {};
var daterangepicker_single_datetime_default_config = {};

var translations = new Map();

function translate(message) {
    if (translations.has(message)) {
        return translations.get(message);
    }

    return message;
}

function addTranslations(messages) {
  //  console.log(messages);
    for (let [key, value] of Object.entries(messages)) {
        translations.set(key,value);
    }
}

$(document).ready(function(){
    console.log(translations);
});

