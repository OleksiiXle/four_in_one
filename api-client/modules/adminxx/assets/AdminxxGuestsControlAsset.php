<?php

namespace app\modules\adminxx\assets;

use yii\web\AssetBundle;

class AdminxxGuestsControlAsset extends  AssetBundle {
    public $baseUrl = '@web/modules/adminxx/assets';
    public $sourcePath = '@app/modules/adminxx/assets';
    public $css = [
    ];
    public $js = [
        'js/guestsControl.js',
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