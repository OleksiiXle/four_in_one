<?php

namespace app\modules\adminxx\controllers;

use app\components\AccessControl;
use app\modules\adminxx\models\MenuXX;
use app\modules\adminxx\models\Route;

/**
 * Class MenuxController
 * Редактирование меню
 * @package app\modules\adminxx\controllers
 */
class MenuxController extends MainController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'menu', 'get-menux'
                    ],
                    'roles'      => ['adminMenuEdit', ],
                ],
            ],
        ];
        return $behaviors;
    }


    /**
     * @return string
     */
    public function actionMenu()
    {
      //  $rout = new Route();
       // $routes = $rout->getAppRoutes();
        return $this->render('menuEdit');
    }

    /**
     * AJAX Возвращает вид _menuxInfo для показа информации по выбранному get-menux
     * @return string
     */
    public function actionGetMenux($id = 0)
    {

        $model = MenuXX::findOne($id);
        if (isset($model)){
            return $this->renderAjax('@app/modules/adminxx/views/menux/_menuxInfo', [
                'model' => $model,
            ]);
        } else {
            return 'Not found';
        }
    }



}