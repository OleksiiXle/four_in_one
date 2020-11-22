<?php
namespace common\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot/assets';
    public $sourcePath = '@common/assets';
    public $publishOptions = ['forceCopy' => true];

    public $css = [
        'css/site.css',
        'css/common.css',
      //  'datepicker/css/daterangepicker.css',
    ];
    public $js = [
        'js/site.js',
    //    'datepicker/js/moment-with-locales.min.js',
   //     'datepicker/js/daterangepicker.js',
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
