<?php

namespace app\controllers;

use apiserver\models\LoginForm;
use apiserver\modules\oauth2\AuthorizeFilter;

class AuthController extends \yii\web\Controller {

    public function behaviors() {
        return [
            /**
             * Checks oauth2 credentions and try to perform OAuth2 authorization on logged user.
             * AuthorizeFilter uses session to store incoming oauth2 request, so
             * you can do additional steps, such as third party oauth authorization (Facebook, Google ...)
             */
            'oauth2Auth' => [
                'class' => AuthorizeFilter::className(),
                'only'  => ['index'],
            ],
        ];
    }

    public function actions() {
        return [
            /**
             * Returns an access token.
             */
            'token' => [
                'class' => \app\controllers\actions\OAuth2TokenAction::classname(),
            ],
                /**
                 * OPTIONAL
                 * Third party oauth providers also can be used.
                 */
                /* 'back' => [
                  'class' => \yii\authclient\AuthAction::className(),
                  'successCallback' => [$this, 'successCallback'],
                  ], */
        ];
    }

    /**
     * Display login form, signup or something else.
     * AuthClients such as Google also may be used
     */
    public function actionIndex() {
        $rec['METHOD'] = \Yii::$app->request->getMethod();
        $rec['HEADERS'] = \Yii::$app->request->headers;
        $rec['RAW_BODY'] = \Yii::$app->request->rawBody;
        $rec['BODY_PARAMS'] = \Yii::$app->request->bodyParams;
        $rec['QUERY_PARAMS'] = \Yii::$app->request->queryParams;
        $rec['COOCIES'] = \Yii::$app->request->cookies;
        if (\Yii::$app->request->isPost){
            $rec['POST'] = \Yii::$app->request->post();
        }
      //  if (\Yii::$app->request->isGet){
     //       $rec['GET'] = \Yii::$app->request->get();
    //    }
        \yii::trace('************************************************ REQUEST', "dbg");
        \yii::trace(\yii\helpers\VarDumper::dumpAsString($rec), "dbg");
        $model = new LoginForm();
        if ($model->load(\Yii::$app->request->post()) && $model->login()) {
            //\yii::trace(\yii\helpers\VarDumper::dumpAsString($model), "dbg");
            \yii::trace('************************************************ LOGIN IS OK', "dbg");
         //   \yii::trace(\yii\helpers\VarDumper::dumpAsString($model), "dbg");
            if ($this->isOauthRequest) {
                $this->finishAuthorization();
            } else {
                return $this->goBack();
            }
        } else {
            \yii::trace('************************************************ LOGIN IS OK', "dbg");

            return $this->render('index',
                            [
                        'model' => $model,
            ]);
        }
    }

    /**
     * OPTIONAL
     * Third party oauth callback sample
     * @param OAuth2 $client
     */
    public function successCallback($client) {
        switch ($client::className()) {
            /*
              case GoogleOAuth::className():
              // Do login with automatic signup
              break;
             */
            //todo
            default:
                break;
        }
        /**
         * If user is logged on, redirects to oauth client with success,
         * or redirects error with Access Denied
         */
        if ($this->isOauthRequest) {
            $this->finishAuthorization();
        }
    }

}
