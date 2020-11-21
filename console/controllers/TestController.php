<?php

namespace console\controllers;

use Yii;

class TestController extends \yii\console\Controller
{
    public function actionIndex()
    {
        echo Yii::$app->id . PHP_EOL;
    }
}