<?php

namespace app\commands;

use yii\console\Controller;
use app\components\models\Translation;

class PostController extends Controller
{
    public function actionInit(){
        $strSql = file_get_contents(__DIR__ . '/data/post.sql');
        $a = \Yii::$app->db->createCommand($strSql)->execute();
        $strSql = file_get_contents(__DIR__ . '/data/post_media.sql');
        $a = \Yii::$app->db->createCommand($strSql)->execute();
    }
}