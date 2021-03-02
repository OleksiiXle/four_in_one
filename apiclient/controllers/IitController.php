<?php
namespace app\controllers;

use common\components\AccessControl;
use Yii;
use yii\web\Controller;

class IitController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'check-bibl'
                    ],
                    'roles'      => [
                        '@', '?'
                    ],
                ],
            ],
        ];

        return $behaviors;
    }


    public function actionCheckBibl()
    {
        $tmp = 1;
        $this->layout = '@app/modules/post/views/layouts/postLayout.php';
        return $this->render('checkBibl', [
          //  'provider' => $provider,
         //   'data' => $data,
        ]);
    }
}
