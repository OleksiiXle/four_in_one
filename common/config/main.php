<?php
$db = require __DIR__ . '/db.php';
$backGroundDb = require(__DIR__ . '/backGroundDb.php');

return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => $db,
        'backGroundDb' => $backGroundDb,
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'vfyrHPxjQZfzATztb4_Lzlclxk0kcRLv',
        ],
        'authManager' => [
            'class' => 'common\components\DbManager', // or use 'yii\rbac\DbManager'
            'cache' => 'cache'
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
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
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

];
