<?php

namespace backend\modules\adminxx\controllers;

use backend\controllers\MainController;
use common\components\AccessControl;
use common\components\models\Configs;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;

/**
 * Class ConfigsController
 * Изменение системных настроек
 * @package app\modules\adminxx\controllers
 */
class ConfigsController extends MainController
{

    /**
     * @return array
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
                         'update',
                    ],
                    'roles'      => ['adminConfigUpdate' ],
                ],
            ],
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'delete' => ['post'],
            ],

        ];
        return $behaviors;
    }

    /**
     * +++ Изменение системных настроек update
     */
    public function actionUpdate()
    {
        $model = new Configs();
        $model->getConfigs();

        if (\Yii::$app->getRequest()->isPost) {
            $data = \Yii::$app->getRequest()->post('Configs');
            if (isset($data['reset-button'])){
                return $this->redirect('/adminxx');
            }
            $model->setAttributes($data);
            if ($model->setConfigs()) {
                return $this->redirect('/adminxx');
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * --- Список всех
     * @return mixed
     */
    public function actionIndex() {
       //
        // $configs = new Configs();
    //    $configs->getConfigs();
        $dataProvider = new ActiveDataProvider([
            'query' => Configs::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('index',[
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * --- Регистрация нового
     * @return string
     */
    public function actionCreate()
    {
        $model = new Configs();
        if (\Yii::$app->getRequest()->isPost) {
            $data = \Yii::$app->getRequest()->post('Configs');
            if (isset($data['reset-button'])){
                return $this->redirect(['index']);
            }
            $model->setAttributes($data);
            if ($model->save()) {
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * --- Удаление
     * @return string
     */
    public function actionDelete($id)
    {
        if (\Yii::$app->request->isPost){
            $userDel = Configs::findOne($id)->delete();
            if ($userDel === 0){
                \yii::$app->getSession()->addFlash("warning","Ошибка при удалении.");
            }
        }
        return $this->redirect('index');

    }

    /**
     * ---
     * @return \yii\web\Response
     */
    public function actionUpdateConfigs()
    {
        $configs = new Configs();
        $configs->getConfigs();
        return $this->redirect('index');

    }


}