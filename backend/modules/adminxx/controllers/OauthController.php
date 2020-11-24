<?php

namespace backend\modules\adminxx\controllers;

use backend\modules\adminxx\models\oauth\OauthAccessToken;
use backend\modules\adminxx\models\oauth\OauthAuthorizationCode;
use backend\modules\adminxx\models\oauth\OauthCient;
use backend\modules\adminxx\models\oauth\OauthRefreshToken;
use Yii;
use yii\data\ActiveDataProvider;
use common\components\AccessControl;
use backend\controllers\MainController;
use backend\modules\adminxx\models\RuleX;

/**
 * Class RuleController
 * Редактор правил
 * @package app\modules\adminxx\controllers
 */
class OauthController extends MainController
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
                    'roles'      => ['adminSuper', ],
                ],
            ],
        ];
        return $behaviors;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $dataProviderClient = new ActiveDataProvider([
            'query' => OauthCient::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        $dataProviderAccessToken = new ActiveDataProvider([
            'query' => OauthAccessToken::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        $dataProviderAuthCode = new ActiveDataProvider([
            'query' => OauthAuthorizationCode::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        $dataProviderRefreshToken = new ActiveDataProvider([
            'query' => OauthRefreshToken::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('index', [
            'dataProviderClient' => $dataProviderClient,
            'dataProviderAuthCode' => $dataProviderAuthCode,
            'dataProviderAccessToken' => $dataProviderAccessToken,
            'dataProviderRefreshToken' => $dataProviderRefreshToken,
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
