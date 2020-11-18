<?php

namespace common\widgets\selectXle;

use yii\web\AssetBundle;

class SelectXleAssets extends AssetBundle
{
    public $baseUrl = '@web/common/widgets/selectXle/assets';
    public $sourcePath = '@common/widgets/selectXle/assets';
    public $publishOptions = ['forceCopy' => true];
    public $css = [
        'css/selectXle.css',
    ];
    public $js = [
        'js/selectXle.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
