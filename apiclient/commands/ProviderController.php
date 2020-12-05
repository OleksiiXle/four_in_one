<?php

namespace app\commands;

use app\components\models\Provider;
use yii\console\Controller;
use app\components\models\Translation;

class ProviderController extends Controller
{

    public function actionInit()
    {
        $providers = require(__DIR__ . '/../config/providers.php');
      //  echo var_dump($providers) . PHP_EOL;
        foreach ($providers as $name => $properties) {
            $provider = new Provider();
            $provider->scenario = Provider::SCENARIO_INSTALL;
          //  $provider->setAttributes($properties);
            $provider->name = $name;
            $provider->class = (!empty($properties['class'])) ? $properties['class'] : null;
            $provider->client_id = (!empty($properties['client_id'])) ? $properties['client_id'] : null;
            $provider->client_secret = (!empty($properties['client_secret'])) ? $properties['client_secret'] : null;
            $provider->token_url = (!empty($properties['token_url'])) ? $properties['token_url'] : null;
            $provider->auth_url = (!empty($properties['auth_url'])) ? $properties['auth_url'] : null;
            $provider->signup_url = (!empty($properties['signup_url'])) ? $properties['signup_url'] : null;
            $provider->api_base_url = (!empty($properties['api_base_url'])) ? $properties['api_base_url'] : null;
            $provider->scope = (!empty($properties['scope'])) ? $properties['scope'] : null;
            $provider->state_storage_class = (!empty($properties['state_storage_class'])) ? $properties['state_storage_class'] : null;
       //     echo var_dump($properties) . PHP_EOL;
        //    echo $name . PHP_EOL;
            echo var_dump($provider->getAttributes()) . PHP_EOL;
            if (!$provider->save()) {
                echo var_dump($provider->getErrors()) . PHP_EOL;
                exit();
            }
        }

    }



}