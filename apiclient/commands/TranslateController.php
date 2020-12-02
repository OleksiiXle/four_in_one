<?php

namespace app\commands;

use yii\console\Controller;
use app\components\models\Translation;

class TranslateController extends Controller
{

    public function actionInit(){
        $translations = require(__DIR__ . '/data/transRusInit.php');
        $t = Translation::deleteAll();
        $a = \Yii::$app->db->createCommand('ALTER TABLE translation AUTO_INCREMENT=1')->execute();
        $tkey = 1;
        foreach ($translations as $translation){
            foreach ($translation as $language => $message){
                echo $tkey . ' ' . $language . ' ' . $message . PHP_EOL;
                $t = new Translation();
                $t->category = 'app';
                $t->tkey = $tkey;
                $t->language = $language;
                $t->message = $message;
                if (!$t->save()){
                    echo var_dump($t->getErrors());
                    echo PHP_EOL;
                    return false;
                }
            }
            $tkey++;
        }


    }



}