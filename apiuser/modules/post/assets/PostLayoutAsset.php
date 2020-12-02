<?php

namespace frontend\modules\post\assets;

use yii\web\AssetBundle;

class PostLayoutAsset extends  AssetBundle {
    public $baseUrl = '@web/modules/post/assets';
    public $sourcePath = '@app/modules/post/assets';
    public $publishOptions = ['forceCopy' => true];
    public $css = [
        'css/post.css',
    ];
    public $js = [
        'js/layout.js',
        'js/post.js',
    ];
    public $jsOptions = array(
        'position' => \yii\web\View::POS_HEAD
    );
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}