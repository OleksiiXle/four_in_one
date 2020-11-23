<?php

namespace app\modules\adminxx\assets;

use yii\web\AssetBundle;

class AdminxxUserAsset extends  AssetBundle {
    public $baseUrl = '@web/modules/adminxx/assets';
    public $sourcePath = '@app/modules/adminxx/assets';
    public $publishOptions = ['forceCopy' => true];
    public $css = [
       // 'css/menu.css',
       // 'mdl/material.css'

    ];
    public $js = [
        'js/user.js',
      //  'mdl/material.js',
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