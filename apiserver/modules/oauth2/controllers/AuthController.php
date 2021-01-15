<?php

namespace apiserver\modules\oauth2\controllers;

use apiserver\modules\oauth2\Exception;
use yii\web\Controller;
use common\helpers\Functions;
use apiserver\modules\oauth2\AuthorizeFilter;
use apiserver\modules\oauth2\models\LoginForm;
use apiserver\modules\oauth2\models\SignupForm;
use apiserver\modules\oauth2\TokenAction;

class AuthController extends \yii\rest\Controller
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
                'class' => AuthorizeFilter::class,
                'only' => ['index', 'signup'],
            ],
        ];
    }
    public function actions()
    {
        return [
            // returns access token
            'token' => [
                'class' => TokenAction::class,
            ],
        ];
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        $this->layout = '@common/views/layouts/loginLayout.php';
        Functions::log("SERVER API --- AuthController public function actionIndex()", true);

        $model = new LoginForm();
        if ($model->load(\Yii::$app->request->post()) && $model->login()) {
            Functions::log("SERVER API --- пришла заполненная форма логина");

            if ($this->isOauthRequest) {
                Functions::log("SERVER API --- пользователь залогинился ОК");

                $this->finishAuthorization();
            } else {
                Functions::log("SERVER API --- пользователь НЕ залогинился - пароль и имя не совпали");
                return $this->goBack();
            }


            return $this->goBack();
        } else {
            Functions::log("SERVER API --- выводим форму для логина ...");

            return $this->render('index', [
                'model' => $model,
            ]);
        }
    }

    public function actionSignup()
    {
        $this->layout = false;
        Functions::logRequest();

        $model = new SignupForm();
        $model->scenario = SignupForm::SCENARIO_SIGNUP_BY_API;
        if ($model->load(\Yii::$app->request->post()) && $model->signup()) {
            if ($this->isOauthRequest) {
                Functions::log('**** пользователь зарегистрировался ОК');

                $this->finishAuthorization();
            } else {
                Functions::log('**** пользователь НЕ зарегистрировался ');
                return $this->goBack();
            }


            return $this->goBack();
        } else {
            return $this->render('signup', [
                'model' => $model,
            ]);
        }
    }

    public function actionError()
    {
        Functions::log('****************** error action');
        throw new Exception('exeption');
    }
}

