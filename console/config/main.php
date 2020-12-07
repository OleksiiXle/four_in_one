<?php
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$backGroundDb = require(__DIR__ . '/backGroundDb.php');


/*
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
 //   require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);
*/
return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
          ],
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'db' => 'db',
        ],
        'migrate-client' => [
            'class' => 'yii\console\controllers\MigrateController',
            'db' => 'clientDb',

            //  'templateFile' => '@app/views/migrations/migration_db.php',
            //   'migrationPath' => '@app/migrations/db'
        ],
        'adminxx-client' => [
            'class' => 'console\controllers\AdminxxController',
            'db' => 'clientDb',
        ],
        'translate-client' => [
            'class' => 'console\controllers\TranslateController',
            'db' => 'clientDb',
        ],
    ],
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'cache' => 'cache'
        ],
        'authManagerClient' => [
            'class' => 'yii\rbac\DbManager',
            'cache' => 'cache',
            'db' => 'clientDb',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => $db,
        'backGroundDb' => $backGroundDb,
        'clientDb' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=xle_client',
            'username' => 'root',
            'password' => '111',
            'charset' => 'utf8',
        ],

    ],
    'params' => $params,
];
