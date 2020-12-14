<?php

namespace common\widgets\menuAction;

use yii\web\AssetBundle;

class MenuActionAsset extends AssetBundle {
    public $baseUrl = '@web/common/widgets/menuAction/assets';
    public $sourcePath = '@common/widgets/menuAction/assets';

    public $js = [
        'js/menuAction.js',
    ];
    public $css = [
        'css/menuAction.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
?>
