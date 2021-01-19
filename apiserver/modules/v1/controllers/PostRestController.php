<?php

namespace apiserver\modules\v1\controllers;

use Yii;
use common\helpers\Functions;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use \apiserver\components\AccessControl;
use apiserver\modules\oauth2\TokenAuth;
use yii\web\ForbiddenHttpException;

class PostRestController extends ActiveController
{
    public $modelClass = 'apiserver\modules\v1\models\Post';

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
                        'index',  'grid', 'test',
                    ],
                    'roles'      => ['@', '?' ],
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'create', 'update', 'delete', 'view',
                    ],
                    'roles'      => ['postCRUD', ],
                    /*
                    'denyCallback' => function($rule, $action) {
                        throw new ForbiddenHttpException(\Yii::t('yii', 'You are not allowed to perform this action.'));
                    },
                    */
                ],
            ],
        ];
        return $behaviors;
    }


    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => 'apiserver\modules\v1\actions\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'prepareDataProvider' => function($action, $filter){
                     return $this->prepareDataProvider($action, $filter);
                }
            ],
            'view' => [
                'class' => 'apiserver\modules\v1\actions\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'create' => [
                'class' => 'apiserver\modules\v1\actions\CreateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->createScenario,
            ],
            'update' => [
                'class' => 'apiserver\modules\v1\actions\UpdateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],
            'delete' => [
                'class' => 'apiserver\modules\v1\actions\DeleteAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'options' => [
                'class' => 'apiserver\modules\v1\actions\OptionsAction',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    /**
     * Checks the privilege of the current user.
     *
     * This method should be overridden to check whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param string $action the ID of the action to be executed
     * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     * @throws ForbiddenHttpException if the user does not have access
     */
    public function checkAccess($action, $model = null, $params = [])
    {
    }

    public function prepareDataProvider($action, $filter)
    {
        $requestParams = Yii::$app->getRequest()->getBodyParams();

        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }

        /* @var $modelClass \yii\db\BaseActiveRecord */
        $modelClass = $this->modelClass;

        $query = $modelClass::find();
        if (isset($requestParams['filter'])) {
            foreach ($requestParams['filter'] as $attribute => $params) {
                $query->andWhere([$params['condition'], $attribute, $params['value']]);
            }
        }

        $dataProvider = Yii::createObject([
            'class' => ActiveDataProvider::className(),
            'query' => $query,
            'pagination' => [
                'params' => $requestParams,
            ],
            'sort' => [
                'params' => $requestParams,
            ],
        ]);

        return $dataProvider;

    }

}


