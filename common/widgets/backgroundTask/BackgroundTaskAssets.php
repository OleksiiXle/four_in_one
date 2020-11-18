<?php

namespace common\widgets\backgroundTask;

use yii\web\AssetBundle;

class BackgroundTaskAssets extends AssetBundle
{
    public $baseUrl = '@web/common/widgets/backgroundTask/assets';
    public $sourcePath = '@common/widgets/backgroundTask/assets';
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
