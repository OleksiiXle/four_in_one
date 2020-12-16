<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/params.php'
);

$db = require __DIR__ . '/db.php';
$backGroundDb = require(__DIR__ . '/backGroundDb.php');


$config = [
    'id' => 'app-api-server',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'apiserverr\controllers',
    'defaultRoute' => 'site/index',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',

    'modules' => [
        'v1' => [
            'class' => 'apiserver\modules\v1\V1',
        ],
        'oauth2' => [
            'class' => 'apiserver\modules\oauth2\Module',
        ],
    ],

    'components' => [
        'request' => [
           // 'csrfParam' => '_csrf-apiuser',
            'cookieValidationKey' => 'aHdm_vwbUjfbe0OTPD8mpoBGDd5V-x0K',
            'baseUrl'=>'/dstest/apiserver', //todo раскомментировать в случае одного хоста

        ],
        'user' => [
            //'class' => 'apiserver\modules\oauth2\models\UserYii',
            'class' => 'common\components\UserX',
           // 'identityClass' => 'apiserver\modules\oauth2\models\UserIdenty',
            'identityClass' => 'common\models\User',
            'loginUrl' => ['site/login'],
            'enableAutoLogin' => false,
            'enableSession' => false,
            'identityCookie' => ['name' => '_identity-apiserver', 'httpOnly' => true],
        ],
        'authManager' => [
            'class' => 'apiserver\components\ApiDbManager',
            'cache' => 'cache'
        ],
        'db' => $db,
        'backGroundDb' => $backGroundDb,
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'configs' => [
            'class' => 'common\components\ConfigsComponent',
        ],
        'session' => [
            // this is the name of the session cookie used for login on the apiuser
            'name' => 'api-server',
        ],
        'log' => [
            //  'traceLevel' => YII_DEBUG ? 3 : 0,
            'traceLevel' => 1,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'trace', 'info'],
                    'categories' => ['dbg'],
                    'logFile' => '@runtime/dbg/dbg.log',
                    'logVars' => [],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction'=>'v1/system/error',
            'class'=>'yii\web\ErrorHandler',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'i18n' => [
            'translations' => [
                'conquer/oauth2' => [
                    'class' => \yii\i18n\PhpMessageSource::class,
                    'basePath' => '@conquer/oauth2/messages',
                ],
            ],
        ]
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

