<?php

namespace app\widgets\menuX;

use app\modules\adminxx\models\MenuXX;
use yii\widgets\InputWidget;

class MenuXWidget extends InputWidget
{
    public $showLevel=0;
    public $accessLevels = [0];


    public function init()
    {
       // parent::init();
    }

    public function run()
    {
        $i=1;
        if (\Yii::$app->user->isGuest){
            $menus = [''];
        } else {
            $user_id = \Yii::$app->user->getId();
          //  $userAssignments = \Yii::$app->getAuthManager()->getAssignments($user_id);
            $userAssignments = \Yii::$app->getAuthManager()->getPermissionsByUser($user_id);
            $menus = ['menuAll'];
            foreach ($userAssignments as $name => $data){
                if (substr($name, 0,4) === 'menu'){
                    $menus[] = $name;
                }
            }
        }
        MenuXAssets::register($this->getView());
        $query = MenuXX::find()
            ->orderBy('parent_id, sort')
            ->where(['in', 'role', $menus])
            ->andWhere(['in', 'access_level', $this->accessLevels])
            ;
        $tree = $query
            ->asArray()
            ->all();

        $html = MenuXX::getTree($tree,0, $this->showLevel);

        return $this->render('menuX',
            [
                'html' => $html,
            ]);
    }

}
