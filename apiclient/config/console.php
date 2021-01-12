<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$backGroundDb = require(__DIR__ . '/backGroundDb.php');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@tests' => '@app/tests',
        '@common'   => dirname(dirname(__DIR__)) . '/common',
        '@console'   => dirname(dirname(__DIR__)) . '/console',
    ],

    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'cache' => 'cache'
        ],

        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'backGroundDb' => $backGroundDb,
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
            //'apiBaseUrl' => $providers['xapi']['api_base_url'], //todo брать из authClientCollection
        ],

    ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
