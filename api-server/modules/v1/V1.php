<?php

namespace apiserver\modules\v1;

class V1 extends \yii\base\Module {
    public $controllerNamespace = 'apiserver\modules\v1\controllers';

    public function init()
    {
        parent::init();

        // custom initialization code goes here
        \Yii::$app->setComponents([
            /*
            'urlManager' => [
                'class' => 'yii\web\UrlManager',
                'enablePrettyUrl' => true,
                'enableStrictParsing' => true,
                'showScriptName' => false,
                'rules' => [
                    [
                        'class' => 'yii\rest\UrlRule',
                        'controller' => 'v1/user',
                        'pluralize' => false,
                        'except' => ['delete', 'post', 'put', ''],
                    ],
                ],
            ],
            'request' => [
                'class' => 'yii\web\Request',
                'cookieValidationKey' => 'bBlVg2eb_z1rlmjkAgfhO6lo4otSDI3Smwa',
                'parsers' => [
                    'application/json' => 'yii\web\JsonParser',
                ],
            ],
            */
            'errorHandler' => [
                'errorAction'=>'v1/system/error',
                'class'=>'yii\web\ErrorHandler',
            ],
            /*'user' => [
                'class' => 'yii\web\User',
                'identityClass' => 'app\modules\adminpanel\models\Administrator',
                'loginUrl' => '/adminpanel/default/login',
            ],
            */
        ]);
    }
}