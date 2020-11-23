<?php

namespace app\widgets\menuX;
use yii\web\AssetBundle;

class MenuXAssets extends AssetBundle
{
    public $baseUrl = '@web/widgets/menuX/assets';
    public $sourcePath = '@app/widgets/menuX/assets';
    public $publishOptions = ['forceCopy' => true];
    public $css = [
       'css/menuX.css',
    ];
    public $js = [
        'js/menuX.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
