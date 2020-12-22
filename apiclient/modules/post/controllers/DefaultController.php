<?php
namespace app\modules\post\controllers;

use app\modules\post\models\Post;
use Yii;
use common\components\AccessControl;
use yii\data\ActiveDataProvider;

class DefaultController extends MainController
{
   /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'index',
                    ],
                    'roles'      => [ '@', '?'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        $tmp=1;
        return $this->render('index', [
          //  'dataProvider'  => $dataProvider,
        ]);
    }
}
