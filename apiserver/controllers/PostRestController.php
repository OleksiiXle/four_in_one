<?php

namespace apiserver\controllers;

use common\helpers\Functions;
use yii\rest\ActiveController;

class PostRestController extends ActiveController
{
    public $modelClass = 'apiserver\models\PostRest';

    public function beforeAction($action)
    {
        Functions::logRequest();
        return parent::beforeAction($action);
    }
}
