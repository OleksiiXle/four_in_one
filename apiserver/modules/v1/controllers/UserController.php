<?php

namespace apiserver\modules\v1\controllers;

use common\helpers\Functions;
use common\models\UserM;
use apiserver\modules\oauth2\TokenAuth;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\rest\Controller;
use yii\web\Response;

class UserController extends Controller
{
    public function behaviors()
    {
        return [
            // performs authorization by token
            'tokenAuth' => [
                'class' => TokenAuth::class,
            ],
        ];
    }
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }
    protected function verbs()
    {
        return [
            'userinfo' => ['POST', 'GET'],
        ];
    }

    public function actionUserinfo()
    {
        Functions::log('SERVER API ---  class UserController public function actionUserinfo()');
        Functions::logRequest();
        if ($userId = \Yii::$app->request->post('id')){
            $user = UserM::findOne($userId);
            $userProfile = $user->userProfileForApi;
            Functions::log('SERVER API ---  возвращаем:');
            Functions::log($userProfile);
            return $userProfile;
        } elseif (\Yii::$app->request->isGet) {
            if (!\Yii::$app->user->isGuest && $user = UserM::findOne(\Yii::$app->user->getId())) {
                $userProfile = $user->userProfileForApi;
                Functions::log('SERVER API ---  возвращаем:');
                Functions::log($userProfile);
                return $userProfile;
            }
            throw new NotFoundHttpException("User not authorized");
        } else {
            throw new NotFoundHttpException("User not found");
        }
    }


}
