<?php

namespace apiserver\modules\oauth2\controllers;

use yii\web\Controller;
use common\helpers\Functions;
use apiserver\modules\oauth2\AuthorizeFilter;
use apiserver\modules\oauth2\models\LoginForm;
use apiserver\modules\oauth2\TokenAction;

class AuthController extends Controller
{
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        $tmp = 1;
        return [
            /**
             * checks oauth2 credentions
             * and performs OAuth2 authorization, if user is logged on
             */
            'oauth2Auth' => [
                'class' => AuthorizeFilter::className(),
                'only' => ['index'],
            ],
        ];
    }
    public function actions()
    {
        return [
            // returns access token
            'token' => [
                'class' => TokenAction::classname(),
            ],
        ];
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        $this->layout = false;
        Functions::logRequest();

        $model = new LoginForm();
        if ($model->load(\Yii::$app->request->post()) && $model->login()) {
            if ($this->isOauthRequest) {
                Functions::log('**** пользователь залогинился ОК');

                $this->finishAuthorization();
            } else {
                Functions::log('**** пользователь НЕ залогинился - пароль и имя не совпали');
                return $this->goBack();
            }


            return $this->goBack();
        } else {
            return $this->render('index', [
                'model' => $model,
            ]);
        }
    }
}

