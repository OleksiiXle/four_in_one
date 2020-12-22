<?php

namespace app\modules\adminxx\controllers;

use app\controllers\MainController;
use app\components\AccessControl;
use common\components\models\Configs;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\Url;

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
                return $this->redirect(Url::toRoute('/adminxx'));
            }
            $model->setAttributes($data);
            if ($model->setConfigs()) {
                return $this->redirect(Url::toRoute('/adminxx'));
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
                return $this->redirect(Url::toRoute('index'));
            }
            $model->setAttributes($data);
            if ($model->save()) {
                return $this->redirect(Url::toRoute('index'));
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
        return $this->redirect(Url::toRoute('index'));

    }

    /**
     * ---
     * @return \yii\web\Response
     */
    public function actionUpdateConfigs()
    {
        $configs = new Configs();
        $configs->getConfigs();
        return $this->redirect(Url::toRoute('index'));

    }


}