<?php
namespace app\commands;

use app\commands\backgroundTasks\models\BackgroundTask;
use app\components\conservation\models\Conservation;
use app\components\models\Provider;
use app\models\UserApi;
use app\models\UserToken;
use Yii;
use common\models\MenuXX;
use common\models\UserData;
use common\models\UserM;
use common\components\models\Translation;

class InitController extends \yii\console\Controller
{
    const PATH_TO_BACKGROUND_TASKS_LOGS = '/backgroundTasks';

    /**
     * Очистка таблиц базы данных апи-сервера
     * @throws \yii\db\Exception
     */
    public function actionRemoveData()
    {
        echo 'Очистка базы данных ...' .PHP_EOL;
        echo 'Удаление ролей и разрешений' .PHP_EOL;
        $auth = \Yii::$app->authManager;
       $auth->removeAll(); //todo CLEAR ALL

        echo 'Удаление пунктов меню' .PHP_EOL;
        $delCnt = MenuXX::deleteAll();
        echo 'Удалено ' . $delCnt . ' пунктов меню ' .PHP_EOL;
        $a = \Yii::$app->db->createCommand('ALTER TABLE menu_x AUTO_INCREMENT=1')->execute();

        echo 'Удаление пользователей' .PHP_EOL;
        $delCnt = UserM::deleteAll();
        $a = \Yii::$app->db->createCommand('ALTER TABLE user AUTO_INCREMENT=1')->execute();
        $a = \Yii::$app->db->createCommand('ALTER TABLE user_data AUTO_INCREMENT=1')->execute();
        $a = \Yii::$app->db->createCommand('ALTER TABLE u_control AUTO_INCREMENT=1')->execute();
        echo 'Удалено пользователей ' . $delCnt . PHP_EOL;

        echo 'Удаление переводов из словаря' .PHP_EOL;
        $delCnt = Translation::deleteAll();
        $a = \Yii::$app->db->createCommand('ALTER TABLE translation AUTO_INCREMENT=1')->execute();
        echo 'Удалено переводов ' . $delCnt . PHP_EOL;

        echo 'Удаление background_task' .PHP_EOL;
        $delCnt = BackgroundTask::deleteAll();
        $a = \Yii::$app->db->createCommand('ALTER TABLE background_task AUTO_INCREMENT=1')->execute();
        echo 'Удалено background_task ' . $delCnt . PHP_EOL;

        echo 'Удаление conservation' .PHP_EOL;
        $delCnt = Conservation::deleteAll();
        $a = \Yii::$app->db->createCommand('ALTER TABLE conservation AUTO_INCREMENT=1')->execute();
        echo 'Удалено conservation ' . $delCnt . PHP_EOL;

        echo 'Удаление user_token' .PHP_EOL;
        $delCnt = UserToken::deleteAll();
        $a = \Yii::$app->db->createCommand('ALTER TABLE user_token AUTO_INCREMENT=1')->execute();
        echo 'Удалено user_token ' . $delCnt . PHP_EOL;

        echo 'Удаление user_api' .PHP_EOL;
        $delCnt = UserApi::deleteAll();
        $a = \Yii::$app->db->createCommand('ALTER TABLE user_api AUTO_INCREMENT=1')->execute();
        echo 'Удалено user_api ' . $delCnt . PHP_EOL;

        echo 'Удаление provider' .PHP_EOL;
        $delCnt = Provider::deleteAll();
        $a = \Yii::$app->db->createCommand('ALTER TABLE provider AUTO_INCREMENT=1')->execute();
        echo 'Удалено provider ' . $delCnt . PHP_EOL;

     //   echo 'Удаление oauth2_client' .PHP_EOL;
     //   $delCnt = OauthCient::deleteAll();
    //    echo 'Удалено oauth2_client ' . $delCnt . PHP_EOL;
    }

    public function actionAddData()
    {
        echo 'Наполнение базы данных тестовыми данными ...' .PHP_EOL;

        $this->permissionsInit();
        $this->menuInit();
        $this->usersInit();
        $this->providersInit();
    }

    private function permissionsInit()
    {
        echo '***********************************************************************' . PHP_EOL;
        echo 'Создание ролей и разрешений' . PHP_EOL;
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
            echo '* разрешение * ' . $permission ;
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
            echo '* наследники роли * ' . $role . PHP_EOL;
            $parentRole = $auth->getRole($role);
            foreach ($children as $child) {
                echo ' добавляю' . ' ' . $child . PHP_EOL;
                try{
                    $childRole = $auth->getRole($child);
                    $auth->addChild($parentRole, $childRole);
                } catch (\yii\base\Exception $e){
                    echo ' уже есть' . ' ' . $child . PHP_EOL;
                }
            }

        }
        //-- добавляем ролям разрешения, которых не было
        foreach ($rolesPermissions as $role => $permission) {
            echo '* разрешения роли * ' . $role . PHP_EOL;
            $parentRole = $auth->getRole($role);
            foreach ($permission as $perm) {
                echo ' добавляю' . ' ' . $perm ;
                try{
                    $rolePermission = $auth->getPermission($perm);
                    if (isset($rolePermission)) {
                        $auth->addChild($parentRole, $rolePermission);
                        echo ' OK' . PHP_EOL;
                    } else {
                        echo ' упс... такого разрешения еще нет' . PHP_EOL;
                        exit();
                    }
                } catch (\yii\base\Exception $e){
                    echo ' такое разрешение уже есть' . ' ' . $perm . PHP_EOL;
                }
            }

        }
        return true;
    }

    private function menuInit() {
        echo '***********************************************************************' . PHP_EOL;
        echo 'Создание МЕНЮ' . PHP_EOL;
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

    public function usersInit()
    {
        echo '***********************************************************************' . PHP_EOL;
        echo 'Создание тестовых пользователей' . PHP_EOL;
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
                echo 'Добавлен ...' . PHP_EOL;
            } else {
                // echo var_dump($oldUser->first_name) . PHP_EOL;
                echo 'Уже есть ...' . PHP_EOL;
            }
        }
        return true;
    }

    public function providersInit()
    {
        echo 'Провайдеры АПИ ...' .PHP_EOL;

        $providers = require(__DIR__ . '/../config/providers.php');
        //  echo var_dump($providers) . PHP_EOL;
        foreach ($providers as $name => $properties) {
            $provider = new Provider();
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