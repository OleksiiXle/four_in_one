<?php

namespace app\helpers;

use Yii;
use app\modules\adminxx\models\UserM;

class AccessHelper
{

    /**
     * !!! ADMINX Запись в сессию аутентификационных данных пользователя
     * userPermissions - все разрешения и роуты
     * userRoutes - разрешенные роуты
     * userRoles - роли
     * @param bool $refresh - с очисткой
     */
    static public function saveUserPermissionsToSession__($refresh = true)
    {
        $session = \Yii::$app->session;
        if ($refresh){
            if (! $session->get('userPermissions')){
                $session->remove('userPermissions');
            }
            if (! $session->get('userRoutes')){
                $session->remove('userRoutes');
            }
            if (! $session->get('userRoles')){
                $session->remove('userRoles');
            }
        }
        if(!$session->has('userPermissions')
            || !$session->has('userRoutes')
            || !$session->has('userRoles') ){
            $user_id = \yii::$app->user->getId();
            $auth = \yii::$app->authManager;
            $permItems=$auth->getPermissionsByUser($user_id);
            $permissions = $routes = $roles = [];
            foreach ($permItems as $item){
                $permissions[$item->name] = (!empty($item->ruleName)) ? $item->ruleName : '';

                //  $permissions[$item->name] = true;
                if ($item->name[0] === '/') {
                    $routes[$item->name] = true;
                }
            }
            $roleItems = $auth->getRolesByUser($user_id);
            foreach ($roleItems as $item){
                $roles[$item->name] = true;
                $permissions[$item->name] = (!empty($item->ruleName)) ? $item->ruleName : '';

            }
            $session->set('userPermissions', $permissions);
            $session->set('userRoutes', $routes);
            $session->set('userRoles', $roles);
        }
    }

    /**
     * Запись в сессию зазрешений юсера, подразделений, доступных пользователю
     * @param bool $refresh - с очисткой
     */
    static public function saveUserPermissionsToSession($refresh = true)
    {
        $session = \Yii::$app->session;
        //-- если надо - очищаем данные сессии
        if ($refresh){
            if (! $session->get('userAssigments')){
                $session->remove('userAssigments');
            }
        }
        //-- если в сессии нет аутентификационных данных - записываем их туда
        if(!$session->has('userAssigments')){
            $user = UserM::findOne(\Yii::$app->user->getId());
            $session->set('userAssigments', array_flip(array_keys(Yii::$app->authManager->getPermissionsByUser($user->id))));
        }

    }


    /**
     * !!! ADMINX Возвращает аутентификационные данные пользователя из сессии
     * если $renew - по предварительное обновление
     * @param string $item  userPermissions || userRoutes || userRoles
     * @return array
     */
    static public function getUserAutentificationData($item, $renew=false)
    {
        $result = [];
        if ($renew){
            self::saveUserPermissionsToSession(true);
        }
        $session = \Yii::$app->session;
        if ($session->has($item)){
            $result = $session->get($item);
        }
        return $result;
    }

    /**
     * !!! Возвращает массив разрешений текущего пользователя
     * @return array
     */
    static public function getUserPermissions()
    {
        $userPermissions = [];
        $session = \Yii::$app->session;
        if ($session->has('userPermissions')){
            $userPermissions = $session->get('userPermissions');
        }
        return $userPermissions;
    }

    /**
     * !!! @deprecated Возвращает массив разрешений текущего пользователя
     * @return array
     */
    static public function getUserAssigments(){
        $userAssigments = [];
        $session = \Yii::$app->session;
        if ($session->has('userPermissions')){
            $userAssigments = $session->get('userPermissions');
        }
        return $userAssigments;
    }

    /**
     * Пытается найти $fieldName в фильтрах, $_GET, $_POST
     * @param string $fieldName Имя поля, по которому производить поиск
     * @return integer|null
     */
    static public function searchParam($fieldName = "department_id") {
        $result = null;
        $_get       = \yii::$app->request->get();
        $_post      = \yii::$app->request->post();
        if (isset($_get["filter"]) && array_key_exists($fieldName,
                $_get["filter"])) {
            $result = $_get["filter"][$fieldName];
        } elseif (isset($_get[$fieldName])) {
            $result = $_get[$fieldName];
        } elseif (isset($_post[$fieldName])) {
            $result = $_post[$fieldName];
        }
        return $result;
    }

    /**
     * Осуществляет проверку прав доступа.
     * 
     * @param string|array $roles
     * @example "roleName"
     * @example ["roleName", "permissionName"]
     * @example ["roleName", "permissionName" => ["paramName" => "paramValue"]]
     * 
     * @param array $params
     * @return boolean
     */
    public static function checkAccess($roles = "", $params = []) {
        if (is_string($roles)) {
            return \yii::$app->user->can($roles, $params);
        }
        if (is_array($roles)) {
            $checkResult = false;
            foreach ($roles as $key => $role) {
                if (is_scalar($role)) {
                    $checkResult = $checkResult | static::checkAccess($role,
                                    $params);
                } else {
                    $checkResult = $checkResult | static::checkAccess($key,
                                    $role);
                }
                if ($checkResult)
                    return true;
            }
        }
        return $checkResult;
    }

}
