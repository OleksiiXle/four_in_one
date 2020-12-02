<?php

namespace apiuser\modules\post\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 */
class PostsListAsset extends AssetBundle
{
    public $basePath = '@web/modules/post/assets';
    public $sourcePath = '@app/modules/post/assets';

    public $css = [
        'css/postsList.css',
    ];
    public $js = [
       // 'js/menux.js',
        'js/postsList.js',

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
