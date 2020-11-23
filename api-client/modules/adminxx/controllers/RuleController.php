<?php

namespace app\modules\adminxx\controllers;

use Yii;
use app\components\AccessControl;
use app\modules\adminxx\models\RuleX;
use yii\data\ActiveDataProvider;

/**
 * Class RuleController
 * Редактор правил
 * @package app\modules\adminxx\controllers
 */
class RuleController extends MainController
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
                        'index',
                    ],
                    'roles'      => ['adminAuthItemList', ],
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'create', 'update', 'delete'
                    ],
                    'roles'      => ['adminAuthItemCRUD', ],
                ],
            ],
        ];
        return $behaviors;
    }

    /**
     * +++ Список правил index
     * @return string
     */
    public function actionIndex()
    {
        $query = RuleX::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * +++ Создание нового правила create
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new RuleX();
        $rulesClasses = RuleX::getRulesClasses();
        if (Yii::$app->request->isPost){
            $_post = Yii::$app->request->post();
            if (!empty($_post['doAction'])){
                if ($model->load(Yii::$app->request->post())  ) {
                    if (!$model->addRule()){
                        return $this->render('update', ['model' => $model, 'rulesClasses' => $rulesClasses]);
                    }
                    // Helper::invalidate();
                }
            }
            return $this->redirect('index');
        }
        return $this->render('update', ['model' => $model, 'rulesClasses' => $rulesClasses]);
    }


    /**
     * +++ Удаление правила delete
     * @param  string $id
     * @return string
     */
    public function actionDelete($id)
    {
        if (Yii::$app->request->isPost){
            $model = RuleX::getRule($id);
            $model->delete();
        }
        return $this->redirect('index');

    }

    /**
     * @deprecated  Updates an existing AuthItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param  string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = RuleX::getRule($id);
        $model->mode = 'update';
        $rulesClasses = RuleX::getRulesClasses();
        if (Yii::$app->request->isPost){
            $_post = Yii::$app->request->post();
            if (!empty($_post['doAction'])){
                if ($model->load(Yii::$app->request->post())  ) {
                    if (!$model->save()){
                        return $this->render('update', [
                            'model' => $model,
                        ]);
                    }
                    // Helper::invalidate();
                }
            }
            return $this->redirect('index');
        }
        return $this->render('update', ['model' => $model,]);
    }
}
