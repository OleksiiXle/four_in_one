<?php

namespace app\widgets\backgroundTask;

use yii\web\AssetBundle;

class BackgroundTaskAssets extends AssetBundle
{
    public $baseUrl = '@web/widgets/backgroundTask/assets';
    public $sourcePath = '@app/widgets/backgroundTask/assets';
    public $publishOptions = ['forceCopy' => true];
    public $css = [
        'css/backgroundTask.css',
    ];
    public $js = [
        'js/backgroundTask.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
