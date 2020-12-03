<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$backGroundDb = require(__DIR__ . '/backGroundDb.php');

$config = [
    'id' => 'apiclient',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
 //   'defaultRoute' => 'adminxx',
    'language' => 'ru-RU',
    'defaultRoute' => 'post',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@common'   => dirname(dirname(__DIR__)) . '/common',
],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'vfyrHPxjQZfzATztb4_Lzlclxk0kcRLv',
            'baseUrl'=>'/apiclient', //todo раскомментировать в случае одного хоста
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'authClientCollection' => [
            'class'   => 'yii\authclient\Collection',
            'clients' => [
                'xapi' => [
                    'class'        => 'app\components\XapiAuthClient',
                    /*
                    'clientId'     => 'xapi',
                    'clientSecret' => '123',
                    'tokenUrl'     => 'http://api.server/oauth2/auth/token',
                    'authUrl'      => 'http://api.server/oauth2/auth/index',
                    // 'authUrl'      => 'http://api.server/oauth2/index?expand=email',
                    'apiBaseUrl'   => 'http://api.server/v1',
                    */
                    'clientId'     => $params['clientId'],
                    'clientSecret' => $params['clientSecret'],
                    'tokenUrl'     => $params['tokenUrl'],
                    'authUrl'      => $params['authUrl'],
                    'signupUrl'      => $params['signupUrl'],
                    'apiBaseUrl'   => $params['apiBaseUrl'],

                    'stateStorage' => 'app\components\XapiStateStorage'
                ],
            ],
        ],
        'xapi'  => [
            'class'      => 'app\components\XapiV1Client',
            'apiBaseUrl' => $params['apiBaseUrl'],
        ],
        'authManager' => [
            'class' => 'app\components\DbManager', // or use 'yii\rbac\DbManager'
            'cache' => 'cache'
        ],
        'user' => [
            'class' => 'app\models\UserX',
            'identityClass' => 'app\models\User',
            'loginUrl' => ['site/login'],
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-apiuser', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the apiuser
            'name' => 'apiclient',
        ],
        'configs' => [
            'class' => 'app\components\ConfigsComponent',
        ],
        'conservation' => [
            'class' => 'app\components\conservation\ConservationComponent',
        ],
        'userProfile' => [
            'class' => 'app\components\UserProfileComponent',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'i18n' => [
            'translations' => [
                'app' => [
                    //  'class' => 'yii\i18n\DbMessageSource',
                    'class' => 'app\components\DbMessageSource',
                    //   'class' => 'app\i18n\PhpMessageSource',
                    //'basePath' => '@app/messages',
                    'sourceLanguage' => 'ru-RU',
                    /*
                    'fileMap' => [
                        'app'       => 'app.php',
                        'app/error' => 'error.php',
                    ],
                    */
                ],
            ],
        ],

        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'backGroundDb' => $backGroundDb,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
    ],
    'modules' => [
        'adminxx' => [
            'class' => 'app\modules\adminxx\Adminxx',
        ],
        'post' => [
            'class' => 'app\modules\post\Post',
        ],
    ],
    'as access' => [
        'class' => 'app\components\AccessControl',
        /*
        'allowActions' => [
            'site/error',
            'debug/*',
            'gii/*',
        ]
        */
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
