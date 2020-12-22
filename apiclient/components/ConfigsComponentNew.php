<?php

namespace app\components;

use common\components\models\Configs;
use common\components\models\Customization;
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