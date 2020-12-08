<?php
namespace common\widgets\xgrid;

use yii\web\AssetBundle;

class XgridAsset extends AssetBundle {
    public $baseUrl = '@web/common/widgets/xgrid/assets';
    public $sourcePath = '@common/widgets/xgrid/assets';
    public $publishOptions = ['forceCopy' => true];

    public $js = [
        'js/xgrid.js',
    ];
    public $css = [
        'css/xgrid.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
?>
