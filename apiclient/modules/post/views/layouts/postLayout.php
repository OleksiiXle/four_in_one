<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\widgets\menuX\MenuXWidget;
use app\widgets\changeLanguage\ChangeLanguageWidget;
use app\modules\post\assets\PostLayoutAsset;
use app\assets\AppAsset;
//use app\assets\BackgroundTaskAsset;
use yii\jui\JuiAsset;
use app\helpers\DateHelper;

$absoluteBaseUrl = Url::base(true);
$this->registerJs("
    const _BASE_URL = '{$absoluteBaseUrl}';
",\yii\web\View::POS_HEAD);

AppAsset::register($this);
PostLayoutAsset::register($this);

//BackgroundTaskAsset::register($this);
JuiAsset::register($this);

if (Yii::$app->session->getAllFlashes()){
         $fms = Yii::$app->session->getAllFlashes();
         $_fms = \yii\helpers\Json::htmlEncode($fms);
         $this->registerJs("var _fms = {$_fms};",\yii\web\View::POS_HEAD);
}

?>
<?php
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => \yii\helpers\Url::toRoute(['/images/sun_61831.png'])]);?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
<?php $this->beginBody() ;?>

<div id="mainContainer" class="container-fluid">
    <!--************************************************************************************************************* HEADER-->
    <div class="xLayoutHeader">

        <!--************************************************************************************************************* MENU BTN-->
        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2" align="left" style="padding-left: 2px; padding-right: 0">
            <!--  <button id="open-menu-btn" onclick="showModal(500,600, 'lokoko the best');" class="xMenuBtn" >-->
            <a href="<?=\yii\helpers\Url::toRoute('/adminxx')?>" title="На гоговну сторінку">
                 <span class ="img-rounded">
                        <img  src="<?=\yii\helpers\Url::toRoute('@web/images/sun_61831.png');?>" height="40px" width="40px;">
                 </span>
            </a>
            <button id="open-menu-btn" onclick="showMenu();" class="xMenuBtn" >
                  <span class="glyphicon glyphicon-list" ></span>
              </button>
          </div>
          <!--************************************************************************************************************* CENTER-->

        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 " >
            <h3 style="margin-top: 15px;margin-bottom: 15px; white-space: nowrap; overflow: hidden;"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 " >
            <?php
            echo ChangeLanguageWidget::widget();
            ?>
        </div>
        <!--************************************************************************************************************* LOGIN/LOGOUT-->
        <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1" align="center" style="padding-left: 1px">
            <?php
            if (!Yii::$app->user->isGuest){
                $icon = \yii\helpers\Url::toRoute('@web/images/log_logout_door_1563.png');
                echo Html::beginForm(['/adminxx/user/logout'], 'post');
                echo Html::submitButton(
                    '<span> <img  src="' . $icon . '" height="30px" width="30px;">' . Yii::$app->user->getIdentity()->username .  '</span>',
                    ['class' => 'btn btn-link ']
                );
                echo Html::endForm();
            }
            ?>
        </div>
        <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1" align="center" style="padding-left: 1px">
            <?php
            if (!empty(Yii::$app->user->getApiLoginsInfo())){
                echo Html::beginForm(['/site/logout-from-api'], 'post');
                echo Html::submitButton('Logout from API<br>' . Yii::$app->user->getApiLoginsInfo(),
                    ['class' => 'btn btn-link ']
                );
                echo Html::endForm();
            }
            ?>
        </div>

    </div>
    <div class="xLayoutContent">

        <div id="flashMessage" style="display: none">
        </div>

        <?= $content ?>
        <div class="xFooter">
            <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                <p>О сколько нам открытий чудных готовит просвещенья дух</p>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                <div>
                    И опыт, сын ошибок трудных
                </div>
                <div>
                    И гений, просвещенья друг ...
                    <br>
                    А.С. Пушкин
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                <br>
                 Lokoko inc. LTD - <?= date('Y') ?>
            </div>
        </div>
    </div>

</div>


<div id="xWrapper">
    <div id="xCover" ></div>
    <div id="xMenu" onclick="menuClick()">
        <div id="xMenuContent" >
        <button class="xMenuCloseBtn" onclick="hideMenu();">
            <span class ="img-rounded">
                <img  src="<?=\yii\helpers\Url::toRoute('@web/images/sun_61831.png');?>" height="50px" width="50px;">
            </span>

        </button>
        <div class="menuTree">
            <?php
            echo MenuXWidget::widget([
                'showLevel' => '1',
                'accessLevels' => [0,2]
            ]) ;
            ?>
        </div>

    </div>
    </div>
    <div id="xModal">
        <div id="xModalWindow">
            <table class="table xModalHeader">
                <tr>
                    <td>
                      <span id="xModalHeader"></span>

                    </td>
                    <td align="right">
                        <button id="xModalCloseBtn" onclick="hideModal();">
                            <span class="glyphicon glyphicon-remove-circle" ></span>
                        </button>
                    </td>
                </tr>
            </table>
            <div id="xModalContent">
                <b>lokoko</b>
            </div>
        </div>
    </div>


</div>


<div id="preloaderCommonLayout" style="display: none">
    <div class="page-loader-circle"></div>
    <div id="preloaderText"></div>
</div>


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
<script>
    moment.locale('ru');

    date_format = '<?= DateHelper::SYSTEM_DATE_FORMAT_JS;?>';
  //  date_format = 'MM/DD/YYYY';
    datetime_format = '<?= DateHelper::SYSTEM_DATETIME_FORMAT_JS;?>';

    daterangepicker_default_ranges = {
        'Сегодня': [moment(), moment()],
        'Вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'За последние 7 дней': [moment().subtract(6, 'days'), moment()],
        'За последние  30 дней': [moment().subtract(29, 'days'), moment()],
        'В этом месяце': [moment().startOf('month'), moment().endOf('month')],
        'В прошлом месяце': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        /*
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        */
    };

    daterangepicker_locale_config = {
        direction: 'ltr',
        format: datetime_format,
        separator: ' - ',
        applyLabel: 'Применить',
        cancelLabel: 'Отмена',
        weekLabel: 'Нед.',
        customRangeLabel: 'Произвольный диапазон',
        daysOfWeek: moment.weekdaysMin(),
        monthNames: moment.monthsShort(),
        firstDay: moment.localeData().firstDayOfWeek()
    };

    daterangepicker_default_config = {
        startDate : moment().startOf('day'),
        endDate : moment().endOf('day'),
        minDate : false,
        maxDate : false,
        maxSpan : false,
        autoApply : false,
        singleDatePicker : false,
        showDropdowns : false,
        minYear : moment().subtract(100, 'year').format('YYYY'),
        maxYear : moment().add(100, 'year').format('YYYY'),
        showWeekNumbers : false,
        showISOWeekNumbers : false,
        showCustomRangeLabel : true,
        timePicker : false,
        timePicker24Hour : false,
        timePickerIncrement : 1,
        timePickerSeconds : false,
        linkedCalendars : true,
        autoUpdateInput : true,
        alwaysShowCalendars : true,
        ranges : daterangepicker_default_ranges,
        opens: 'center',
        locale: daterangepicker_locale_config
    };

    daterangepicker_single_default_config = {
        startDate : moment().startOf('day'),
        endDate : moment().endOf('day'),
        minDate : false,
        maxDate : false,
        maxSpan : false,
        autoApply : false,
        singleDatePicker : true,
        showDropdowns : false,
        minYear : moment().subtract(100, 'year').format('YYYY'),
        maxYear : moment().add(100, 'year').format('YYYY'),
        showWeekNumbers : false,
        showISOWeekNumbers : false,
        showCustomRangeLabel : false,
        timePicker : false,
        timePicker24Hour : false,
        timePickerIncrement : 1,
        timePickerSeconds : false,
        linkedCalendars : true,
        autoUpdateInput : true,
        alwaysShowCalendars : true,
        ranges : {},
        opens: 'center',
        locale: daterangepicker_locale_config
    };

    daterangepicker_datetime_locale_config = {
        direction: 'ltr',
        format: datetime_format,
        separator: ' - ',
        applyLabel: 'Применить',
        cancelLabel: 'Отмена',
        weekLabel: 'W',
        customRangeLabel: 'Произвольный диапазон',
        daysOfWeek: moment.weekdaysMin(),
        monthNames: moment.monthsShort(),
        firstDay: moment.localeData().firstDayOfWeek(),
    };

    daterangepicker_single_datetime_default_config = {
        singleDatePicker: true,
        showDropdowns: true,
        timePicker: true,
        autoUpdateInput: false,
       // timePicker24Hour: $.parseJSON(''),
      //  timePickerSeconds: $.parseJSON(''),
        locale: daterangepicker_datetime_locale_config,
        maxDate: '<?= DateHelper::getFormattedDateFromString(DateHelper::SYSTEM_DATETIME_FORMAT, '+40 year');?>',
    };

</script>


