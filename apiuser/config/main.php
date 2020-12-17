<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/params.php'
);

$config = [
    'id' => 'app-apiuser',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'apiuser\controllers',
    'bootstrap' => ['log'],
    'defaultRoute' => 'post',
    'language' => 'ru-RU',
    'components' => [
        'request' => [
          //  'csrfParam' => '_csrf-apiuser',
            'cookieValidationKey' => 'aHdm_vwbUjfbe0OvbncvbnTPD8mpoBGDd5V-x0K',
            'baseUrl'=>'/dstest/apiuser', //todo раскомментировать в случае одного хоста

        ],
        'session' => [
            // this is the name of the session cookie used for login on the apiuser
            'name' => 'advanced-apiuser',
        ],
        'user' => [
            'class' => 'common\components\UserX',
            'identityClass' => 'common\models\User',
            'loginUrl' => ['site/login'],  //todo********************* resolve it ****************
            'enableAutoLogin' => false,
        ],

        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            //     'scriptUrl'=>'/apiadmin/index.php',
            'rules' => [
                /*
                '<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>' => '<module>/<controller>/<action>',
                '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
                '<module:\w+>/<controller:\w+>' => '<module>/<controller>/index',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                '<controller:\w+>' => '<controller>/index',
                */
                //        '<controller:\w+>/<action:\w+>/' => '<controller>/<action>',
            ],
        ],

    ],
    'modules' => [
        'post' => [
            'class' => 'apiuser\modules\post\Post',
        ],
    ],
    'params' => $params,
];

if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;

