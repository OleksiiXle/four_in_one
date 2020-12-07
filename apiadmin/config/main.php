<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/params.php'
);

$config = [
    'id' => 'app-apiadmin',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'apiadmin\controllers',
    'bootstrap' => ['log'],
    'defaultRoute' => 'adminxx',
    'language' => 'ru-RU',
 //   'homeUrl' => '/apiadmin',
    'components' => [
        'request' => [
           // 'csrfParam' => '_csrf-apiadmin',
            'cookieValidationKey' => 'aHdm_vwbUjfbe0OTPD8mpoBGDd5V-x0K',
            'baseUrl'=>'/dstest/apiadmin', //todo раскомментировать в случае одного хоста
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],

        'session' => [
            // this is the name of the session cookie used for login on the apiadmin
            'name' => 'advanced-apiadmin',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'user' => [
            'class' => 'common\components\UserX',
            'identityClass' => 'common\models\User',
            'loginUrl' => ['site/login'],  //todo********************* resolve it ****************
            'enableAutoLogin' => false,
        ],

    ],
    'modules' => [
        'adminxx' => [
            'class' => 'apiadmin\modules\adminxx\Adminxx',
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
        'allowedIPs' => ['*'],
    ];

}

return $config;
