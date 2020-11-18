<?php

namespace backend\modules\adminxx\models;

use Yii;
use yii\base\BaseObject;

class Assignment extends BaseObject
{
    /**
     * @var integer User id
     */
    public $id;
    /**
     * @var \yii\web\IdentityInterface User
     */
    public $user;

    /**
     * @inheritdoc
     */
    public function __construct($id, $user = null, $config = array())
    {
        $this->id = $id;
        $this->user = $user;
        parent::__construct($config);
    }

    /**
     * +++ Назначение пользователю роли, разрешения.
     * @param array $items
     * @return integer number of successful grand
     */
    public function assign($items)
    {
        $manager = Yii::$app->getAuthManager();
        $success = 0;
        foreach ($items as $name) {
            try {
                $item = $manager->getRole($name);
                $item = $item ? : $manager->getPermission($name);
                $manager->assign($item, $this->id);
                $success++;
            } catch (\Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        return $success;
    }

    /**
     * +++ Удаление у пользователя роли, разрешения
     * @param array $items
     * @return integer number of successful revoke
     */
    public function revoke($items) {
        $manager = Yii::$app->getAuthManager();
        $success = 0;
        foreach ($items as $name) {
            try {
                $item = $manager->getRole($name);
                $item = $item ? : $manager->getPermission($name);
                $manager->revoke($item, $this->id);
                $success++;
            } catch (\Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        return $success;
    }

    /**
     * Get all avaliable and assigned roles/permission
     * @return array
     */
    public function getItems()
    {
        $manager = Yii::$app->getAuthManager();
        $avaliable = [];
        foreach (array_keys($manager->getRoles()) as $name) {
            $avaliable[$name] = 'role';
        }

        foreach (array_keys($manager->getPermissions()) as $name) {
            if ($name[0] != '/') {
                $avaliable[$name] = 'permission';
            }
        }

        $assigned = [];
        foreach ($manager->getAssignments($this->id) as $item) {
            $assigned[$item->roleName] = $avaliable[$item->roleName];
            unset($avaliable[$item->roleName]);
        }

        return[
            'avaliable' => $avaliable,
            'assigned' => $assigned
        ];
    }

    /**
     * +++ Получение разрешений по типу
     * @return array
     */
    public function getItemsByType($type)
    {
        $manager = Yii::$app->getAuthManager();
        $avaliable = [];
        $assigned = [];
        switch ($type){
            case 'Roles':
                $assigned     = array_keys($manager->getRolesByUser($this->id));
                $avaliableAll = array_keys($manager->getRoles());
                $avaliable    = array_diff($avaliableAll , $assigned);
                break;
            case 'Permissions':
                $buffAssigned     = array_keys($manager->getPermissionsByUser($this->id));
                $buffAvaliableAll = array_keys($manager->getPermissions());
                $assigned = $avaliableAll =[];
                foreach ($buffAssigned as $name) {
                    if ($name[0] != '/') {
                        $assigned[] = $name;
                    }
                }
                foreach ($buffAvaliableAll as $name) {
                    if ($name[0] != '/') {
                        $avaliableAll[] = $name;
                    }
                }
                $avaliable =  array_diff($avaliableAll , $assigned);
                break;
            case 'Routs':
                $buffAssigned     = array_keys($manager->getPermissionsByUser($this->id));
                $buffAvaliableAll = array_keys($manager->getPermissions());
                $assigned = $avaliableAll =[];
                foreach ($buffAssigned as $name) {
                    if ($name[0] == '/') {
                        $assigned[] = $name;
                    }
                }
                foreach ($buffAvaliableAll as $name) {
                    if ($name[0] == '/') {
                        $avaliableAll[] = $name;
                    }
                }
                $avaliable =  array_diff($avaliableAll , $assigned);

                break;
        }
        $result =[
                'avaliable' => $avaliable,
                'assigned' => $assigned
        ];


        return $result;
    }

    /**
     * +++ Получение ролей и разрешений пользователя
     * @return array
     */
    public function getItemsX()
    {
        $manager = Yii::$app->getAuthManager();
        //--  роли
        $assigned     = array_keys($manager->getRolesByUser($this->id));
        $avaliableAll = array_keys($manager->getRoles());
        $avaliable    = array_diff($avaliableAll , $assigned);
        $result['Roles']=[
            'avaliable' => $avaliable,
            'assigned' => $assigned
        ];
        //-- разрешения
        $buffAssigned     = array_keys($manager->getPermissionsByUser($this->id));
        $buffAvaliableAll = array_keys($manager->getPermissions());
        $assigned = $avaliableAll =[];
        foreach ($buffAssigned as $name) {
            if ($name[0] != '/') {
                $assigned[] = $name;
            }
        }
        foreach ($buffAvaliableAll as $name) {
            if ($name[0] != '/') {
                $avaliableAll[] = $name;
            }
        }
        $avaliable =  array_diff($avaliableAll , $assigned);
        $result['Permissions']=[
            'avaliable' => $avaliable,
            'assigned' => $assigned
        ];
        return $result;
    }

    /**
     * +++ Получение ролей и разрешений пользователя
     * @return array
     */
    public function getItemsXle()
    {
        $manager = Yii::$app->getAuthManager();
        //--  роли
        $assigned     = array_keys($manager->getRolesByUser($this->id));
        $avaliableAll = array_keys($manager->getRoles());
        $avaliable    = array_diff($avaliableAll , $assigned);
        $result['Roles']=[
            'avaliable' => $avaliable,
            'assigned' => $assigned
        ];
        //-- разрешения
        $buffAssigned     = array_keys($manager->getPermissionsByUser($this->id));
        $buffAvaliableAll = array_keys($manager->getPermissions());
        $assigned = $avaliableAll =[];
        foreach ($buffAssigned as $name) {
            if ($name[0] != '/') {
                $assigned[] = $name;
            }
        }
        foreach ($buffAvaliableAll as $name) {
            if ($name[0] != '/') {
                $avaliableAll[] = $name;
            }
        }
        $avaliable =  array_diff($avaliableAll , $assigned);
        //-- определение разрешений (своих и отдельно помечаем из ролей)
        $assignedOwn = $assignedByRole = [];
        $assigments = $manager->getAssignments($this->id);
        foreach ($assigments as $assigment){
            if (in_array($assigment->roleName, $result['Roles']['assigned'])){
                $rolePermissions = array_keys($manager->getPermissionsByRole($assigment->roleName));
                foreach ($rolePermissions as $rolePermission){
                    $assignedByRole[] = '*' . $assigment->roleName . '->' . $rolePermission;
                }
            } else {
                $assignedOwn[] = $assigment->roleName;
            }

        }
        $assigned=array_merge($assignedOwn, $assignedByRole);
        $result['Permissions']=[
            'avaliable' => $avaliable,
            'assigned' => $assigned
        ];
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if ($this->user) {
            return $this->user->$name;
        }
    }
}
