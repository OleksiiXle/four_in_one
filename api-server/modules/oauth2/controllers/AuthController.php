<?php

namespace apiserver\modules\oauth2\controllers;

use yii\web\Controller;
use common\helpers\Functions;
use apiserver\modules\oauth2\AuthorizeFilter;
use apiserver\modules\oauth2\models\LoginForm;
use apiserver\modules\oauth2\TokenAction;

class AuthController extends Controller
{
    public function behaviors()
    {
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
     * Display login form to authorize user
     */
    public function actionIndex()
    {
      //  Functions::dbg();
        $model = new LoginForm();
        if ($model->load(\Yii::$app->request->post()) && $model->login()) {
            if ($this->isOauthRequest) {
             //   \yii::trace('************************************************ isOauthRequest OK', "dbg");

                $this->finishAuthorization();
            } else {
             //   \yii::trace('************************************************ isOauthRequest NOT', "dbg");
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

