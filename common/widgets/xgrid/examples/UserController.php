<?php

namespace apiadmin\modules\adminxx\controllers;

use Yii;
use apiadmin\modules\adminxx\grids\UsersGrid;
use apiadmin\controllers\MainController;
use yii\web\Response;

class UserControllerExample extends MainController
{
    public function actionIndex()
    {
        $usersGrid = new UsersGrid();
        if (Yii::$app->request->isPost) {
            Yii::$app->getResponse()->format = Response::FORMAT_HTML;
            return $usersGrid->reload(Yii::$app->request->post());
        }
        return $this->render('index', [
            'usersGrid' => $usersGrid,
        ]);
    }
}