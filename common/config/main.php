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
        'smtpXleMailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',  // e.g. smtp.mandrillapp.com or smtp.gmail.com
                'username' => 'oleksii.xle.fish@gmail.com',
                //   'login' => 'whitesnake1969',
                'password' => 'fish140269',
                'port' => '587', // Port 25 is a very common port too '567'
                'encryption' => 'tls', // It is often used, check your provider or mail server specs
            ],
            'useFileTransport' => false,
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
