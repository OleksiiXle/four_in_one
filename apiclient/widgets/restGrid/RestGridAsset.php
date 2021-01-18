<?php
namespace app\widgets\restGrid;

use yii\web\AssetBundle;

class RestGridAsset extends AssetBundle {
    public $baseUrl = '@web/app/widgets/restGrid/assets';
    public $sourcePath = '@app/widgets/restGrid/assets';
    public $publishOptions = ['forceCopy' => true];

    public $js = [
        'js/restGrid.js',
    ];
    public $css = [
        'css/restGrid.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
?>
