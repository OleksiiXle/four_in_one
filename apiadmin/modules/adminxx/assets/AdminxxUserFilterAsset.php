<?php

namespace apiadmin\modules\adminxx\assets;

use yii\web\AssetBundle;

class AdminxxUserFilterAsset extends  AssetBundle {
    public $baseUrl = '@web/modules/adminxx/assets';
    public $sourcePath = '@app/modules/adminxx/assets';
    public $publishOptions = ['forceCopy' => true];
    public $css = [
        'css/adminx.css',
    ];
    public $js = [
        'js/userFilter.js',
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