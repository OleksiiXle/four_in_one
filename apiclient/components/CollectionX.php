<?php

namespace app\components;

use app\components\models\Provider;
use yii\base\Component;
use yii\base\InvalidParamException;
use Yii;

class CollectionX extends Component
{
    /**
     * @var \yii\httpclient\Client|array|string HTTP client instance or configuration for the [[clients]].
     * If set, this value will be passed as 'httpClient' config option while instantiating particular client object.
     * This option is useful for adjusting HTTP client configuration for the entire list of auth clients.
     */
    public $httpClient;

    /**
     * @var array list of Auth clients with their configuration in format: 'clientId' => [...]
     */
    private $_clients = [];

    static $proterties = [

    ];


    public function init()
    {
        $providers = Provider::find()
            ->all();
        foreach ($providers as $provider) {
            $tmp = $provider->properties;
            $this->_clients[$provider['name']] = $provider->properties;
        }
    }

    /**
     * @param array $clients list of auth clients
     */
    public function setClients(array $clients)
    {
        $tmp = 1;
        $this->_clients = $clients;
    }

    /**
     * @return ClientInterface[] list of auth clients.
     */
    public function getClients()
    {
        $clients = [];
        foreach ($this->_clients as $id => $client) {
            $clients[$id] = $this->getClient($id);
        }

        return $clients;
    }

    /**
     * @param string $id service id.
     * @return ClientInterface auth client instance.
     * @throws InvalidParamException on non existing client request.
     */
    public function getClient($id)
    {
        $tmp = 1;
        if (!array_key_exists($id, $this->_clients)) {
            throw new InvalidParamException("Unknown auth client '{$id}'.");
        }
        if (!is_object($this->_clients[$id])) {
            $this->_clients[$id] = $this->createClient($id, $this->_clients[$id]);
        }

        return $this->_clients[$id];
    }

    /**
     * Checks if client exists in the hub.
     * @param string $id client id.
     * @return bool whether client exist.
     */
    public function hasClient($id)
    {
        return array_key_exists($id, $this->_clients);
    }

    /**
     * Creates auth client instance from its array configuration.
     * @param string $id auth client id.
     * @param array $config auth client instance configuration.
     * @return ClientInterface auth client instance.
     */
    protected function createClient($id, $config)
    {
        $config['id'] = $id;

        if (!isset($config['httpClient']) && $this->httpClient !== null) {
            $config['httpClient'] = $this->httpClient;
        }

        return Yii::createObject($config);
    }

    public function getClientInfo($name)
    {
        return Provider::find()
            ->where(['name' => $name])
            ->asArray()
            ->one();

    }

}