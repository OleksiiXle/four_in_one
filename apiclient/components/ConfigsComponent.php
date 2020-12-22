<?php

namespace app\components;

use common\components\models\Configs;
use yii\base\Component;

class ConfigsComponent extends Component
{
    private $_adminEmail;
    private $_userControl;
    private $_guestControl;
    private $_guestControlDuration;
    private $_menuType;
    private $_permCacheKey = 'perm';
    private $_permCacheKeyDuration = 0;
    private $_passwordResetTokenExpire = 3600;  //const PASSWORD_RESET_TOKEN_EXPIRE = 3600;
    private $_userDefaultRole = 'user';     //const DEFAULT_ROLE = 'user';
    private $_rbacCacheSource = 'session';//'cache';
    private $_signupWithoutEmailConfirm;

    private $_userProfile = null;
    private $_positionSort = null;

    public $apiProvider = false;


    /*
      'adminEmail',
      'userControl',
      'guestControl',
      'guestControlDuration',
      'menuType',
      'permCacheKey',
      'permCacheKeyDuration',
      'passwordResetTokenExpire',
      'userDefaultRole'
     */
    public function init()
    {
        $this->getItems();
      //  $test = $this->getUserProfile();
        parent::init(); // TODO: Change the autogenerated stub
    }

    /**
     * @return mixed
     */
    public function getAdminEmail()
    {
        if (!isset($this->_adminEmail)){
            $this->getItems();
        }
        return $this->_adminEmail;
    }

    /**
     * @return mixed
     */
    public function getUserControl()
    {
        if (!isset($this->_userControl)){
            $this->getItems();
        }
        return $this->_userControl;
    }

    /**
     * @return mixed
     */
    public function getGuestControl()
    {
        if (!isset($this->_guestControl)){
            $this->getItems();
        }
        return $this->_guestControl;
    }

    /**
     * @return mixed
     */
    public function getSignupWithoutEmailConfirm()
    {
        if (!isset($this->_signupWithoutEmailConfirm)){
            $this->getItems();
        }
        return $this->_signupWithoutEmailConfirm;
    }

    /**
     * @return mixed
     */
    public function getGuestControlDuration()
    {
        if (!isset($this->_guestControlDuration)){
            $this->getItems();
        }
        return (int) $this->_guestControlDuration;
    }

    /**
     * @return mixed
     */
    public function getMenuType()
    {
        if (!isset($this->_menuType)){
            $this->getItems();
        }
        return $this->_menuType;
    }

    /**
     * @return string
     */
    public function getPermCacheKey()
    {
        if (!isset($this->_permCacheKey)){
            $this->getItems();
        }
        return $this->_permCacheKey;
    }

    /**
     * @return int
     */
    public function getPermCacheKeyDuration()
    {
        if (!isset($this->_permCacheKeyDuration)){
            $this->getItems();
        }
        return (int) $this->_permCacheKeyDuration;
    }

    /**
     * @return int
     */
    public function getPasswordResetTokenExpire()
    {
        if (!isset($this->_passwordResetTokenExpire)){
            $this->getItems();
        }
        return (int) $this->_passwordResetTokenExpire;
    }

    /**
     * @return string
     */
    public function getUserDefaultRole()
    {
        if (!isset($this->_userDefaultRole)){
            $this->getItems();
        }
        return $this->_userDefaultRole;
    }

    /**
     * @return string
     */
    public function getRbacCacheSource()
    {
        if (!isset($this->_rbacCacheSource)){
            $this->getItems();
        }
        return $this->_rbacCacheSource;
    }

    protected function getItems()
    {
        $t=1;
        if (empty($this->_adminEmail)
            || empty($this->_userControl)
            || empty($this->_guestControl)
            || empty($this->_guestControlDuration)
            || empty($this->_menuType)
            || empty($this->_permCacheKey)
            || empty($this->_permCacheKeyDuration)
            || empty($this->_passwordResetTokenExpire)
            || empty($this->_userDefaultRole)
            || empty($this->_rbacCacheSource)
            || empty($this->_signupWithoutEmailConfirm)
        ){
            $configs = new Configs();
            $configs->getConfigs();
            $this->_adminEmail = $configs->adminEmail;
            $this->_userControl = ($configs->userControl == 1);
            $this->_guestControl = ($configs->guestControl == 1);
            $this->_signupWithoutEmailConfirm = ($configs->guestControl == 1);
            $this->_guestControlDuration = $configs->guestControlDuration;
            $this->_menuType = $configs->menuType;
            $this->_permCacheKey = $configs->permCacheKey;
            $this->_permCacheKeyDuration = $configs->permCacheKeyDuration;
            $this->_passwordResetTokenExpire = $configs->passwordResetTokenExpire;
            $this->_userDefaultRole = $configs->userDefaultRole;
            $this->_rbacCacheSource = $configs->rbacCacheSource;
        }
    }
}