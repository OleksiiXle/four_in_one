<?php
namespace app\widgets\restGrid;

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Json;

class RestGrid extends GridView
{
    public function run()
    {
        $r=1;
        $view = $this->getView();
        RestGridAsset::register($view);
        parent::run();
    }
}