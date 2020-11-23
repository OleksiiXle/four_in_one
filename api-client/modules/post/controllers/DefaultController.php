<?php
namespace app\modules\post\controllers;

use app\modules\post\models\Post;
use Yii;
use app\components\AccessControl;
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
        $dataProvider = new ActiveDataProvider([
            'query' => Post::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('posts', [
            'dataProvider'  => $dataProvider,
        ]);

        return $this->render('index',
            [
            ]);
    }
}
