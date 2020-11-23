<?php

namespace console\controllers;

use apiclient\modules\adminxx\models\MenuXX;
use apiclient\modules\adminxx\models\UserData;
use apiclient\modules\adminxx\models\UserM;
use Yii;

class InitController extends \yii\console\Controller
{
    public function actionCommonRolesInit()
    {
        echo '*********************************************************************** РОЛИ И  РАЗРЕШЕНМЯ' . PHP_EOL;
        $params = require(__DIR__ . '/data/rbacCommon.php');
        $permissions      = $params['permissions'];
        $roles            = $params['roles'];
        $rolesPermissions = $params['rolesPermissions'];
        $rolesChildren    = $params['rolesChildren'];
        $auth = \Yii::$app->authManagerClient;
        $rolesOld = $auth->getRoles();
        //-- добавляем роли, которых не было
        foreach ($roles as $roleName => $roleNote) {
            echo '* роль * ' . $roleName ;
            $checkRole = $auth->getRole($roleName);
            if (!isset($checkRole)) {
                echo ' добавляю' .PHP_EOL;
                $newRole = $auth->createRole($roleName);
                $newRole->description = $roleNote;
                $auth->add($newRole);
            } else {
                echo ' уже есть' . PHP_EOL;
            }
        }
        //-- добавляем разрешения, которых не было
        foreach ($permissions as $permission => $description) {
            echo '* дозвіл * ' . $permission ;
            $checkRole = $auth->getPermission($permission);
            if (!isset($checkRole)) {
                echo ' добавляю' .PHP_EOL;
                $newPermission = $auth->createPermission($permission);
                $newPermission->description = $description;
                $auth->add($newPermission);
            } else {
                echo ' уже есть' . PHP_EOL;
            }
        }
        //-- добавляем ролям детей, которых не было
        foreach ($rolesChildren as $role => $children) {
            echo '* діти ролі * ' . $role . PHP_EOL;
            $parentRole = $auth->getRole($role);
            foreach ($children as $child) {
                echo ' добавляю' . ' ' . $child . PHP_EOL;
                try{
                    $childRole = $auth->getRole($child);
                    $auth->addChild($parentRole, $childRole);
                } catch (\yii\base\Exception $e){
                    echo ' мабуть вже є така дитинка' . ' ' . $child . PHP_EOL;
                }
            }

        }
        //-- добавляем ролям разрешения, которых не было
        foreach ($rolesPermissions as $role => $permission) {
            echo '* дозвіли ролі * ' . $role . PHP_EOL;
            $parentRole = $auth->getRole($role);
            foreach ($permission as $perm) {
                echo ' добавляю' . ' ' . $perm ;
                try{
                    $rolePermission = $auth->getPermission($perm);
                    if (isset($rolePermission)) {
                        $auth->addChild($parentRole, $rolePermission);
                        echo ' OK' . PHP_EOL;
                    } else {
                        echo ' упс... такого дозвілу ще немає' . PHP_EOL;
                        exit();
                    }
                } catch (\yii\base\Exception $e){
                    echo ' мабуть вже є така дозвіл' . ' ' . $perm . PHP_EOL;
                }
            }

        }
        return true;
    }

    public function actionMenuInit() {
        echo 'МЕНЮ *******************************' .PHP_EOL;
        $delCnt = MenuXX::deleteAll();
        echo 'Удалено ' . $delCnt . ' пунктов меню ' .PHP_EOL;

        $menus = require(__DIR__ . '/data/menuInit.php');
        $sort1 = $sort2 = $sort3 = 1;
        foreach ($menus as $menu1){
            echo $menu1['name'] . PHP_EOL;
            $m1 = new MenuXX();
            $m1->parent_id = 0;
            $m1->sort = $sort1++;
            $m1->name = $menu1['name'];
            $m1->route = $menu1['route'];
            $m1->role = $menu1['role'];
            $m1->access_level = $menu1['access_level'];
            if (!$m1->save()){
                echo var_dump($m1->getErrors()) . PHP_EOL;
                return true;
            }
            foreach ($menu1['children'] as $menu2){
                echo ' --- ' . $menu2['name'] . PHP_EOL;
                $m2 = new MenuXX();
                $m2->parent_id = $m1->id;
                $m2->sort = $sort2++;
                $m2->name = $menu2['name'];
                $m2->route = $menu2['route'];
                $m2->role = $menu2['role'];
                $m2->access_level = $menu2['access_level'];
                if (!$m2->save()){
                    echo var_dump($m2->getErrors()) . PHP_EOL;
                    return true;
                }
                foreach ($menu2['children'] as $menu3){
                    echo ' --- --- ' . $menu3['name'] . PHP_EOL;
                    $m3 = new MenuXX();
                    $m3->parent_id = $m2->id;
                    $m3->sort = $sort3++;
                    $m3->name = $menu3['name'];
                    $m3->route = $menu3['route'];
                    $m3->role = $menu3['role'];
                    $m3->access_level = $menu3['access_level'];
                    if (!$m3->save()){
                        echo var_dump($m3->getErrors()) . PHP_EOL;
                        return true;
                    }
                }
                $sort3 = 1;
            }
            $sort2 = 1;
        }
        return true;
    }

    public function actionUsersInit()
    {
        $users = require(__DIR__ . '/data/userInit.php');
        $auth = \Yii::$app->authManagerClient;
        foreach ($users as $user){
            echo $user['username'] . PHP_EOL;
            $oldUser = UserM::findOne(['username' => $user['username']]);
            if (empty($oldUser)){
                $model = new UserM();
                $model->scenario = UserM::SCENARIO_SIGNUP_BY_ADMIN;
                // $model->setAttributes($user);
                $model->username = $user['username'];
                $model->email = $user['email'];
                $model->password = $user['password'];
                $model->retypePassword = $user['retypePassword'];
                $model->first_name = $user['first_name'];
                $model->middle_name = $user['middle_name'];
                $model->last_name = $user['last_name'];
                $model->setPassword($user['password']);
                $model->generateAuthKey();
                if (!$model->save()){
                    echo var_dump($model->getErrors()) . PHP_EOL;
                    return false;
                }
                $userData = new UserData();
                // $userData->setAttributes($user);


                $userData->user_id = $model->id;
                $userData->emails = $model->email;
                $userData->first_name = $user['first_name'];
                $userData->middle_name = $user['middle_name'];
                $userData->last_name = $user['last_name'];
                if (!$userData->save()){
                    echo var_dump($userData->getErrors()) . PHP_EOL;
                    return false;
                }
                foreach ($user['userRoles'] as $role){
                    $userRole = $auth->getRole($role);
                    if (isset($userRole)){
                        $auth->assign($userRole, $model->id);
                        echo '   ' . $role . PHP_EOL;
                    } else {
                        echo '   не найдена роль - ' . $role . PHP_EOL;
                    }
                }
                echo 'Додано ...' . PHP_EOL;
            } else {
                // echo var_dump($oldUser->first_name) . PHP_EOL;
                echo 'Вже иснуе ...' . PHP_EOL;
            }
        }
        return true;
    }

    public function actionInit(){
        $translations = require(__DIR__ . '/data/transRusInit.php');
        $t = Tr::deleteAll();
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