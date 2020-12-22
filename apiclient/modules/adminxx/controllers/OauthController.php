<?php

namespace app\modules\adminxx\controllers;

use app\modules\adminxx\grids\OauthClientGrid;
use app\modules\adminxx\models\oauth\OauthAccessToken;
use app\modules\adminxx\models\oauth\OauthAuthorizationCode;
use app\modules\adminxx\models\oauth\OauthCient;
use app\modules\adminxx\models\oauth\OauthRefreshToken;
use Yii;
use yii\data\ActiveDataProvider;
use common\components\AccessControl;
use app\controllers\MainController;
use app\modules\adminxx\models\RuleX;
use yii\helpers\Url;

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
                        'index', 'create-oauth-client', 'update-oauth-client', 'delete-oauth-client'
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
        $oauthClientGrid = new OauthClientGrid();
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
            'oauthClientGrid' => $oauthClientGrid,
            'dataProviderAuthCode' => $dataProviderAuthCode,
            'dataProviderAccessToken' => $dataProviderAccessToken,
            'dataProviderRefreshToken' => $dataProviderRefreshToken,
        ]);
    }

    /**
     * +++ Создание нового
     * @return mixed
     */
    public function actionCreateOauthClient()
    {
        $model = new OauthCient();
        if ($model->load(\Yii::$app->getRequest()->post())) {
            if ($model->save()) {
                return $this->redirect(Url::toRoute(['/adminxx/oauth']));
            }
        }
        return $this->render('create',
            [
                'model' => $model,
            ]);
    }

    /**
     * +++ Создание нового
     * @return mixed
     */
    public function actionUpdateOauthClient($client_id)
    {
        $model = OauthCient::findOne($client_id);
        if ($model->load(\Yii::$app->getRequest()->post())) {
            if ($model->save()) {
                return $this->redirect(Url::toRoute(['/adminxx/oauth']));
            }
        }
        return $this->render('update',
            [
                'model' => $model,
            ]);
    }


    /**
     * +++ Удаление правила delete
     * @param  string $id
     * @return string
     */
    public function actionDeleteOauthClient($client_id)
    {
        if (Yii::$app->request->isPost) {
            $model = OauthCient::findOne($client_id);
            $model->delete();
        }
        return $this->redirect(Url::toRoute('index'));

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
            return $this->redirect(Url::toRoute('index'));
        }
        return $this->render('update', ['model' => $model,]);
    }
}
