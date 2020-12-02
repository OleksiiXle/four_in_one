<?php

namespace app\components;

use app\components\models\Configs;
use app\components\models\Customization;
use Yii;
use yii\base\Component;

class ConfigsComponentNew extends Customization
{
    protected function getContainer()
    {
        $configs = new Configs();
        $this->container = $configs->getConfigs();
    }
    protected function setContainer($value){
        $configs = new Configs();
        $this->container = $configs->setConfigs();
   }
}