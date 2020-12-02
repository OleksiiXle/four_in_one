<?php

namespace backend\modules\adminxx\assets;

use yii\web\AssetBundle;

class AdminxxUpdateUserAsset extends  AssetBundle {
    public $baseUrl = '@web/modules/adminxx/assets';
    public $sourcePath = '@app/modules/adminxx/assets';
    public $publishOptions = ['forceCopy' => true];
    public $css = [
        'css/adminx.css',
      //  'mdl/material.css'
    ];
    public $js = [
        'js/updateUser.js',
     //   'mdl/material.js',

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