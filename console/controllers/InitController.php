<?php

namespace console\controllers;

use Yii;

class InitController extends \yii\console\Controller
{
    const PATH_TO_BACKGROUND_TASKS_LOGS = '/backgroundTasks';

    public function actionIndex()
    {
        $basePath = Yii::$app->basePath;
        $basePathClean = str_replace('console', '', Yii::$app->basePath);
        $params = Yii::$app->params;
        //-- создание структуры логов в console
        $dirName = $basePath . DIRECTORY_SEPARATOR . 'runtime/logs';
        echo $dirName;
        if (!is_dir($dirName)) {
            mkdir($dirName, 0777, true);
            echo  ' created' . PHP_EOL;
        } else {
            echo  ' exists' . PHP_EOL;
        }

        $dirName = $basePath . DIRECTORY_SEPARATOR . 'runtime/logs' . self::PATH_TO_BACKGROUND_TASKS_LOGS;
        echo $dirName;
        if (!is_dir($dirName)) {
            mkdir($dirName, 0777, true);
            echo  ' created' . PHP_EOL;
        } else {
            echo  ' exists' . PHP_EOL;
        }

        $dirName .= DIRECTORY_SEPARATOR . 'tmp';
        echo $dirName;
        if (!is_dir($dirName)) {
            mkdir($dirName, 0777, true);
            echo  ' created' . PHP_EOL;
        } else {
            echo  ' exists' . PHP_EOL;
        }

        //-- симлинки
        $baseLogPath = 'runtime/logs' . $params['pathToBackgroundTasksLogs'];
        $pathToLogs = $basePath . DIRECTORY_SEPARATOR .  $baseLogPath;

        foreach ($params['appAliases'] as $appAlias) {
            $dirName = $basePathClean . $appAlias .  '/runtime/logs';
            echo $dirName;
            if (!is_dir($dirName)) {
                mkdir($dirName, 0777, true);
                echo  ' created' . PHP_EOL;
            } else {
                echo  ' exists' . PHP_EOL;
            }

            $pathFromFolderLinkToLogs = $basePathClean
                . $appAlias . DIRECTORY_SEPARATOR . $baseLogPath;
         //   echo $pathToLogs . PHP_EOL;
         //   echo $pathFromFolderLinkToLogs . PHP_EOL;
            exec("ln -s $pathToLogs $pathFromFolderLinkToLogs", $output,$exitCode);
        }
    }


}