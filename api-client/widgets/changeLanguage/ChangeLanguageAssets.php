<?php

namespace app\widgets\changeLanguage;

use yii\web\AssetBundle;

class ChangeLanguageAssets extends AssetBundle
{
    public $baseUrl = '@web/widgets/changeLanguage/assets';
    public $sourcePath = '@app/widgets/changeLanguage/assets';
    public $publishOptions = ['forceCopy' => true];
    public $css = [
        'css/changeLanguage.css',
    ];
    public $js = [
        'js/changeLanguage.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
