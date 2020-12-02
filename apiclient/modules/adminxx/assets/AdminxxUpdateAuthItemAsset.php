<?php

namespace app\modules\adminxx\assets;

use yii\web\AssetBundle;

class AdminxxUpdateAuthItemAsset extends  AssetBundle {
    public $baseUrl = '@web/modules/adminxx/assets';
    public $sourcePath = '@app/modules/adminxx/assets';
    public $publishOptions = ['forceCopy' => true];
    public $css = [
        'css/adminx.css',
    ];
    public $js = [
       // 'js/modalWindows.js',
       // 'js/modalWindows.js',
        'js/updateAuthItem.js',
    ];
    public $jsOptions = array(
        'position' => \yii\web\View::POS_END
    );
    public $depends = [
        //'yii\web\JqueryAsset',
        //'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
    ];
}