<?php

namespace apiserver\modules\v1\controllers;

use yii\rest\Controller;
use yii\web\Response;
use common\components\AccessControl;
use common\helpers\Functions;
use apiserver\modules\oauth2\TokenAuth;
use apiserver\modules\v1\models\Post;

class QueryController extends Controller
{
    const MODELS = [
      'Post' => '\apiserver\modules\v1\models\Post',
    ];

    public function behaviors()
    {
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
                        'index',
                    ],
                    'roles'      => ['@', '?' ],
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

    /**
     * @return string
     */
    public function actionIndex()
    {
        /*
         постом приходит:
[
    'operation' => 'queryScalar'
    'modelName' => 'Post'
    'params' => [
        'fetchMode' => '0'
    ]
    'queryData' => [
        'where' => [
            0 => 'LIKE'
            1 => 'name'
            2 => 'прав'
        ]
        'limit' => '-1'
        'offset' => '-1'
        'emulateExecution' => '0'
        'modelClass' => 'app\\modules\\post\\models\\Post'
    ]
    'selectExpression' => 'COUNT(*)'
]
        или :
[
    'operation' => 'queryAll'
    'modelName' => 'Post'
    'queryData' => [
        'where' => [
            0 => 'LIKE'
            1 => 'name'
            2 => 'прав'
        ]
        'limit' => '1'
        'offset' => '2'
        'orderBy' => [
            'name' => '4'
            'user_id' => '4'
        ]
        'emulateExecution' => '0'
        'modelClass' => 'app\\modules\\post\\models\\Post'
    ]
]

         */
        try {
            $data = \Yii::$app->request->post();
            Functions::log('*************** QUERY ***********');
            Functions::log($data);
            $modelName = self::MODELS[$data['modelName']];
            Functions::log('*************** QUERY DATA***********');
            Functions::log($data['queryData']);
       //     $asArray = (isset($data['queryData']['asArray']) && $data['queryData']['asArray'] == '1') ? true : false;
            $fields = $data['queryData']['fields'] ?? false;
            $extraFields = $data['queryData']['extraFields'] ?? false;

            unset($data['queryData']['modelClass']);
            unset($data['queryData']['fields']);
            unset($data['queryData']['extraFields']);
            if ($fields || $extraFields) {
                unset($data['queryData']['asArray']);
            }
            $model = ($modelName)::find();
            \Yii::configure($model, $data['queryData']);
            switch ($data['operation']) {
                case 'queryAll':
                    if ($fields || $extraFields) {
                        $ret = $model->all();
                        $result = [];
                        foreach ($ret as $item) {
                            $result[] = $item->toArray(($fields ?? []), ($extraFields ?? []));
                        }
                    } else {
                        $result = $model->all();
                    }
                    break;
                case 'queryOne':
                    if ($fields || $extraFields) {
                        $result = $model->one()->toArray(($fields ?? []), ($extraFields ?? []));
                   } else {
                        $result = $model->one();
                    }
                    break;
                case 'queryScalar':
                    $result = $model->select($data['selectExpression'])->scalar();
                    break;
            }
            Functions::log($model);
            Functions::log($result);
        } catch (\Exception $e) {
            $result = $e->getTraceAsString();
            Functions::log('*************** QUERY ERROR ***********');
            Functions::log($result);
        }

        return $result;
    }
}
