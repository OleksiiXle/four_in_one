<?php

namespace console\controllers;

use common\models\MenuXX;
use common\models\UserData;
use common\models\UserM;
use yii\console\Controller;

class AdminxxController extends Controller
{
    public function actionCommonRolesInit()
    {
        echo '*********************************************************************** РОЛИ И  РАЗРЕШЕНМЯ' . PHP_EOL;
        $params = require(__DIR__ . '/data/rbacCommon.php');
        $permissions      = $params['permissions'];
        $roles            = $params['roles'];
        $rolesPermissions = $params['rolesPermissions'];
        $rolesChildren    = $params['rolesChildren'];
        $auth = \Yii::$app->authManager;
      //  $auth->removeAll(); //todo CLEAR ALL
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
        $auth = \Yii::$app->authManager;
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

    public function actionMakeAdmin()
    {
        set_time_limit(0);
        echo 'Создание пользователя с правами администратора' . PHP_EOL;
        echo 'adminSystemTechnical - 1' . PHP_EOL;
        echo 'adminUsers           - 2' . PHP_EOL;
        echo 'adminSystemTechnical - 3' . PHP_EOL;
        echo 'Выбор (1, 2, 3): ';
        $role = fgets(STDIN);
        if (!in_array($role, [1,2,3])){
            echo 'error' . PHP_EOL;
            die();
        }

        echo 'Введите логин:' . PHP_EOL;
        $username = fgets(STDIN);
        $oldUser = UserM::findOne(['username' => $username]);
        if (!empty($oldUser)){
            echo 'Такой пользователь уже есть ' . $username. PHP_EOL;
            die();
        }
        echo 'Введите email:' . PHP_EOL;
        $email = fgets(STDIN);
        $oldUser = UserM::findOne(['username' => $username]);
        if (!empty($oldUser)){
            echo 'Такой email уже есть ' . $email. PHP_EOL;
            die();
        }

        $password = str_shuffle('012345pqrstuvwxyz');
        $data =
            [
            'username' => trim($username),
            'email' => trim($email),
        ];
        $auth = \Yii::$app->authManager;
        $data1 = [];
        switch ($role){
            case 1:
                $userRole = $auth->getRole('adminSystem');
                $data1 = [
                'first_name' => 'Системный',
                'middle_name' => 'Администратор',
                'last_name' => 'Технический',
            ];
            break;
            case 2:
                $userRole = $auth->getRole('adminUsers');
                $data1 = [
                'first_name' => 'Системный',
                'middle_name' => 'Администратор',
                'last_name' => 'Пользователей',
            ];
            break;
            case 3:
                $userRole = $auth->getRole('adminUsersAdvanced');
                $data1 = [
                'first_name' => 'Системный',
                'middle_name' => 'Администратор',
                'last_name' => 'Пользователейрасширенный',
            ];
            break;
        }

        if (!isset($userRole)){
            echo '   не найдена роль ' . PHP_EOL;
            die();
        }

        $data = array_merge($data, $data1);

        $model = new UserM();
        $model->setAttributes($data);
        $model->password = $password;
        $model->retypePassword = $password;
        $model->setPassword($password);
        $model->generateAuthKey();
        if (!$model->validate()){
            echo var_dump($model->getErrors()) . PHP_EOL;
            die();
        }

        if (!$model->save()){
            echo var_dump($model->getErrors()) . PHP_EOL;
            die();
        }
        $auth->assign($userRole, $model->id);



        $userData = new UserData();
        $userData->setAttributes($data);
        $userData->user_id = $model->id;
        if (!$userData->save()){
            echo var_dump($userData->getErrors()) . PHP_EOL;
            die();
        }

        echo 'Ваш пароль:' .  PHP_EOL;
        echo  $password . PHP_EOL;
        die();
    }

    public function actionMakeSuperAdmin()
    {
        set_time_limit(0);
        echo 'Создание пользователя с правами ВСЕХ администраторов' . PHP_EOL;

        echo 'Введите логин:' . PHP_EOL;
        $username = fgets(STDIN);
        $oldUser = UserM::findOne(['username' => $username]);
        if (!empty($oldUser)){
            echo 'Такой пользователь уже есть ' . $username. PHP_EOL;
            die();
        }
        echo 'Введите email:' . PHP_EOL;
        $email = fgets(STDIN);
        $oldUser = UserM::findOne(['username' => $username]);
        if (!empty($oldUser)){
            echo 'Такой email уже есть ' . $email. PHP_EOL;
            die();
        }

        $password = str_shuffle('012345pqrstuvwxyz');
        $data =
            [
            'username' => trim($username),
            'email' => trim($email),
            'first_name' => 'Главный',
            'middle_name' => 'Администратор',
            'last_name' => 'Технический',
            ];
        $auth = \Yii::$app->authManager;

        $model = new UserM();
        $model->scenario = UserM::SCENARIO_SIGNUP_BY_ADMIN;

        //  $model->setAttributes($data);
        $model->username = $data['username'];
        $model->email = $data['email'];
        $model->password = $password;
        $model->retypePassword = $password;
        $model->first_name = $data['first_name'];
        $model->middle_name = $data['middle_name'];
        $model->last_name = $data['last_name'];
        $model->setPassword($password);
        $model->generateAuthKey();
        if (!$model->validate()){
            echo var_dump($model->getErrors()) . PHP_EOL;
            die();
        }

        if (!$model->save()){
            echo var_dump($model->getErrors()) . PHP_EOL;
            die();
        }
        $userRoles = [
            'adminSystem',
            'adminUsers',
            'adminUsersAdvanced',
        ];
        foreach ($userRoles as $role){
            $userRole = $auth->getRole($role);
            if (!isset($userRole)){
                echo '   не найдена роль ' . $role . PHP_EOL;
                die();
            }
            $auth->assign($userRole, $model->id);
        }



        $userData = new UserData();
      //  $userData->setAttributes($data);
        $userData->user_id = $model->id;
        $userData->emails = $model->email;
        $userData->first_name = $data['first_name'];
        $userData->middle_name = $data['middle_name'];
        $userData->last_name = $data['last_name'];

        $userData->user_id = $model->id;
        if (!$userData->save()){
            echo var_dump($userData->getErrors()) . PHP_EOL;
            die();
        }

        echo 'Ваш пароль:' .  PHP_EOL;
        echo  $password . PHP_EOL;
        die();
    }

    public function actionAddTestUsers()
    {
        $user = require(__DIR__ . '/data/testUsersInit.php');
        $auth = \Yii::$app->authManager;
        for ($i = 1; $i < 100; $i++){
            echo $user['username'] . ' - ' . $i . PHP_EOL;
            $model = new UserM();
            //   $model->scenario = User::SCENARIO_REGISTRATION;
            $model->setAttributes($user);
            $model->username = $model->username . $i;
            $model->last_name = $model->last_name . $i;
            $model->email = 'user_' . $i . '@email.com';
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

        }
        return true;
    }


}
