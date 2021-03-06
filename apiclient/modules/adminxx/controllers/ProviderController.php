<?php

namespace app\modules\adminxx\controllers;

use app\components\conservation\ActiveDataProviderConserve;
use app\components\models\Provider;
use app\components\models\Translation;
use app\components\AccessControl;
use app\modules\adminxx\models\filters\ProviderFilter;
use yii\helpers\FileHelper;
use app\modules\adminxx\models\filters\TranslationFilter;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\Url;

class ProviderController extends MainController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                         'index', 'create', 'update', 'delete',
                    ],
                    'roles'      => ['adminSuper' ],
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
     * +++ Список всех
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProviderConserve([
            'filterModelClass' => ProviderFilter::class,
            'conserveName' => 'providerGrid',
            'pageSize' => 10,
        ]);

        return $this->render('index',[
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * +++ Регистрация нового
     * @return string
     */
    public function actionCreate()
    {
        $model = new Provider();
        $model->scenario = Provider::SCENARIO_UPDATE;
        if (\Yii::$app->getRequest()->isPost) {
            $model->load(\Yii::$app->getRequest()->post());
            if ($model->save()) {
                $session = \Yii::$app->session;
                if ($session->get('searchIid')){
                    $session->remove('searchIid');
                }
                $session->set('searchIid', $model->id );

                return $this->redirect(Url::toRoute('index'));
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * +++ Изменение старого
     * @return string
     */
    public function actionUpdate($id)
    {
        $model = Provider::findOne($id);
        $model->scenario = Provider::SCENARIO_UPDATE;
        if (\Yii::$app->getRequest()->isPost) {
            $model->load(\Yii::$app->getRequest()->post());
            if ($model->save()) {
                $session = \Yii::$app->session;
                if ($session->get('searchIid')){
                    $session->remove('searchIid');
                }
                $session->set('searchIid', $model->id );

                return $this->redirect(Url::toRoute('index'));
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * +++ Удаление
     * @return string
     */
    public function actionDelete($id)
    {
        if (\Yii::$app->request->isPost){
            $del = Provider::findOne($id)->delete();
            if ($del === 0){
                \yii::$app->getSession()->addFlash("warning","Ошибка при удалении.");
            }
        }
        return $this->redirect(Url::toRoute('index'));

    }
}