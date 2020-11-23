<?php

namespace apiserver\modules\v1\controllers;

use common\components\AccessControl;
use apiserver\modules\oauth2\TokenAuth;
use apiserver\modules\v1\models\KinoSeans;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\rest\Controller;
use yii\web\Response;

class SaleController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['tokenAuth'] = [
            'class' => TokenAuth::className(),
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'index',
                    ],
                    'roles'      => ['@' ],
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'get-seans', 'get-reservation'
                    ],
                    'roles'      => ['systemAdminxx', ],
                ],
            ],
        ];
        return $behaviors;
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
            'index' => ['GET'],
            'get-seans' => ['GET'],
            'get-reservation' => ['POST'],
        ];
    }

    public function actionIndex()
    {
        $ret = KinoSeans::find()->all();
        return $ret;
    }

    public function actionGetSeans($id)
    {
        $ret = KinoSeans::findOne($id);
        if (isset($ret)){
            return $ret;
        } else {
            throw new NotFoundHttpException();
        }
    }

    public function actionGetReservation()
    {
        if (\Yii::$app->request->isPost){
            $_post = \Yii::$app->request->post();
          //  \yii::trace(\yii\helpers\VarDumper::dumpAsString($_post), "dbg");
            if ($_post['seansId'] && $_post['reservation']){
                $seans = KinoSeans::findOne($_post['seansId']);
                if (!empty($seans)){
                    $ret = $seans->reservation($_post['reservation']);
                    if ($ret['status']){
                        return $ret['data'];
                    } else {
                        throw new NotFoundHttpException($ret['data']);
                    }
                } else {
                    throw new NotFoundHttpException('Сеанс не найден ' . $_post['seansId'] );
                }

            } else {
                throw new BadRequestHttpException('Отсутствуют обязательные параматры');
            }
        } else {
            throw new BadRequestHttpException('Не верный метод передачи запроса');
        }
    }
}
