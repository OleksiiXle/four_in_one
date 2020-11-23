<?php

namespace app\widgets\selectMultiXle;

use yii\web\AssetBundle;

class SelectMultiXleAssets extends AssetBundle
{
    public $baseUrl = '@web/widgets/selectMultiXle/assets';
    public $sourcePath = '@app/widgets/selectMultiXle/assets';
    public $publishOptions = ['forceCopy' => true];
    public $css = [
        'css/selectMultiXle.css',
    ];
    public $js = [
        'js/selectMultiXle.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
