<?php

namespace apiadmin\modules\adminxx\assets;

use yii\web\AssetBundle;

class AdminxxLayoutAsset extends  AssetBundle {
    public $baseUrl = '@web/modules/adminxx/assets';
    public $sourcePath = '@app/modules/adminxx/assets';
    public $publishOptions = ['forceCopy' => true];
    public $css = [
        'css/adminx.css',
        'css/site.css',
        'datepicker/css/daterangepicker.css',
    ];
    public $js = [
        'js/functions.js',
        'js/layout.js',
       // 'datepicker/js/moment.min.js',
        'datepicker/js/moment-with-locales.min.js',
        'datepicker/js/daterangepicker.js',
    ];
    public $jsOptions = array(
        'position' => \yii\web\View::POS_HEAD
    );
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}