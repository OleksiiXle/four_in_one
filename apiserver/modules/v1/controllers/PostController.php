<?php

namespace apiserver\modules\v1\controllers;

use apiserver\modules\v1\models\Post;
use app\modules\v1\models\filters\PostFilter;
use common\helpers\Functions;
use yii\rest\Controller;
use yii\web\Response;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use common\components\AccessControl;
use apiserver\modules\oauth2\TokenAuth;

class PostController extends Controller
{

    public function behaviors()
    {
      //  Functions::logRequest();
        $behaviors = parent::behaviors();
        if (!in_array($this->action->id, [
            'index',
        ]) ) {
            $behaviors['tokenAuth'] = [
                'class' => TokenAuth::class,
            ];
        }
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'index', 'view', 'grid'
                    ],
                    'roles'      => ['@', '?' ],
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'create', 'update', 'delete'
                    ],
                    'roles'      => ['postCRUD', ],
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
            'view' => ['GET'],
            'create' => ['POST'],
            'update' => ['POST'],
            'delete' => ['POST'],
        ];
    }

    public function actionIndex()
    {
        $ret = Post::find()->all();
        return $ret;
    }

    public function actionGrid()
    {
        Functions::log('actionGrid');
        Functions::logRequest();
        $filter = new PostFilter();
        $data = $filter->runClientProviderQuery();
        Functions::log('$data:');
        Functions::log($data);
        if (isset($data)){
            return $data;
        } else {
            throw new NotFoundHttpException();
        }
    }

    public function actionView($id)
    {
        $ret = Post::findOne($id);
        if (isset($ret)){
            return $ret;
        } else {
            throw new NotFoundHttpException();
        }
    }

    public function actionCreate()
    {
        $_post = \Yii::$app->request->post();
        Functions::logRequest();
        return $_post;
    }

    public function actionUpdate()
    {
    }

    public function actionDelete()
    {
    }
}
