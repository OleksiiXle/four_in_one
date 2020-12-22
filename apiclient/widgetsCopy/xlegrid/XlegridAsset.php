<?php
namespace app\widgets\xlegrid;

use yii\web\AssetBundle;

class XlegridAsset extends AssetBundle {
    public $baseUrl = '@web/widgets/xlegrid/assets';
    public $sourcePath = '@app/widgets/xlegrid/assets';
    public $publishOptions = ['forceCopy' => true];

    public $js = [
        'js/xlegrid.js',
    ];
    public $css = [
        'css/xlegrid.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
?>
