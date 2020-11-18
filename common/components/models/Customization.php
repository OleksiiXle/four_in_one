<?php

namespace common\components\models;

abstract class Customization
{
    protected $container = null;

    abstract protected function getContainer();
    abstract protected function setContainer($value);

    public function __set($name, $value)
    {
        $tmp = 1;
        switch ($name) {
            case 'container':
                $this->setContainer($value);
                break;
            default:
                $this->container[$name] = $value;
                $this->setContainer($this->container);
        }
    }

    public function __get($name)
    {
        $tmp = 1;
        switch ($name) {
            case 'container':
                if ($this->container === null) {
                    $this->getContainer();
                }
                return $this->container;
                break;
            default:
                if ($this->container === null) {
                    $this->getContainer();
                }
                if (!empty($this->container[$name])) {
                    return $this->container[$name];
                }
                return false;
       }
    }


}