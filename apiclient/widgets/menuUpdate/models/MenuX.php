<?php

namespace app\widgets\menuUpdate\models;

use app\modules\adminxx\models\MenuXX;
use app\modules\adminxx\models\Route;
use yii\helpers\Html;
use yii\helpers\Url;

class MenuX extends MenuXX
{

    //*****************************************************************************    ДРУГИЕ МЕТОДЫ

    /**
     * Записывает в массив $target идентификаторы всех потомков
     * @param $parent_id
     * @param $target
     * @return bool
     */
    public static function getChildrenIds($parent_id, &$target)
    {
        $children = (new \yii\db\Query)
            ->select(['id', 'parent_id'])
            ->from(self::tableName())
            ->where(['parent_id' => $parent_id])
            ->all();
        if (count($children) > 0) {
            foreach ($children as $child) {
                $target[] = $child['id'];
                self::getChildrenIds($child['id'], $target);
            }
            return true;
        }
    }

    public static function getPermissionsDict() {
        $manager = \Yii::$app->getAuthManager();
        $result['']='';
        $avaliableAll = array_keys($manager->getPermissions());
        foreach ($avaliableAll as $name) {
            if (substr($name, 0, 4) == 'menu') {
                $result[$name] = $name;
            }
        }
        return $result;
    }

    public static function getRoutesDict()
    {
        $rout = new Route();
        $routes = $rout->getAppRoutes();
        return $routes;

    }

    /**
     * Возвращает строку с деревом
     * @param $tree - полный массив дерева
     * @param $pid - корень
     * @return string
     */
    public static function getTree_($tree, $pid){
        $html = '';
        foreach ($tree as $row) {
            if ($row['parent_id'] == $pid) {
                if ($pid > 0){
                    $hasChildren = self::find()->where(['parent_id' => $row['id']])->count();
                    if ($hasChildren){
                        $content = '<a class="node" '
                            . ' onclick="clickAction(this);"'
                            . '> ' . $row['name']
                            . '</a>';
                    } else {
                        $content = Html::a($row['name'], Url::to($row['route'], true),
                            [
                                'class' => 'route',
                            ]);
                    }
                    $html .= '<li>'
                        . $content
                        . self::getTree($tree, $row['id'])
                        . '</li>';

                } else{
                    $html .= self::getTree($tree, $row['id']);

                }
            }
        }
        return $html ? '<ul class="ulMenuX" style="padding-left: 15px">' . $html . '</ul>' : '';
    }

}
