<?php

namespace apiuser\modules\post\assets;

use yii\web\AssetBundle;

class UpdadePostAsset extends  AssetBundle {
    public $baseUrl = '@web/modules/post/assets';
    public $sourcePath = '@app/modules/post/assets';
    public $publishOptions = ['forceCopy' => true];
    public $css = [
       // 'css/post.css',
    ];
    public $js = [
        'js/updatePost.js',
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