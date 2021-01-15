<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$backGroundDb = require(__DIR__ . '/backGroundDb.php');
$providers = require(__DIR__ . '/providers.php');

$config = [
    'id' => 'apiclient',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
 //   'defaultRoute' => 'adminxx',
    'language' => 'ru-RU',
    'timeZone' => 'Europe/Kiev',
    'defaultRoute' => 'post',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@common'   => dirname(dirname(__DIR__)) . '/common',
        '@console'   => dirname(dirname(__DIR__)) . '/console',
],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'vfyrHPxjQZfzATztb4_Lzlclxk0kcRLv',
            'baseUrl'=>'/dstest/apiclient', //todo раскомментировать в случае одного хоста
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'authClientCollection' => [
            'class'   => 'app\components\CollectionX',
        ],
        /*
        'authClientCollection' => [
            'class'   => 'yii\authclient\Collection',
            'clients' => [
                'xapi' => [
                    'class'        => 'app\components\XapiAuthClient',
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
        */
        'xapi'  => [
            'class'      => 'app\components\XapiV1Client',
            'authClientName' => 'xapi',
            //'apiBaseUrl' => $providers['xapi']['api_base_url'], //todo брать из authClientCollection
        ],
        'authManager' => [
            'class' => 'common\components\DbManager', // or use 'yii\rbac\DbManager'
            'cache' => 'cache'
        ],
        'user' => [
            'class' => 'app\models\UserX',
            'identityClass' => 'common\models\User',
            'loginUrl' => ['site/login'],
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-apiuser', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the apiuser
            'name' => 'apiclient',
        ],
        'configs' => [
            'class' => 'common\components\ConfigsComponent',
        ],
        'conservation' => [
            'class' => 'common\components\conservation\ConservationComponent',
        ],
        'userProfile' => [
            'class' => 'common\components\UserProfileComponent',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'i18n' => [
            'translations' => [
                'app' => [
                    //  'class' => 'yii\i18n\DbMessageSource',
                    'class' => 'common\components\DbMessageSource',
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
        'class' => 'common\components\AccessControl',
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
