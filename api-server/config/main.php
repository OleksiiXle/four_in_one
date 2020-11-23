<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/params.php'
);

$config = [
    'id' => 'app-api-server',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'api-server\controllers',
    'modules' => [
        'v1' => [
            'class' => 'api-server\modules\v1\V1',
        ],
        'oauth2' => [
            'class' => 'api-server\modules\oauth2\Module',
        ],
    ],

    'components' => [
        'request' => [
           // 'csrfParam' => '_csrf-frontend',
            'cookieValidationKey' => 'aHdm_vwbUjfbe0OTPD8mpoBGDd5V-x0K',
        ],
        'user' => [
            'class' => 'api-server\modules\oauth2\models\UserYii',
            'identityClass' => 'api-server\modules\oauth2\models\UserIdenty',
            'loginUrl' => ['site/login'],
            'enableAutoLogin' => false,
            'enableSession' => false,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
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

