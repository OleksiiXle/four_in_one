<?php
namespace app\modules\post\controllers;

use app\modules\post\models\Post;
use Yii;
use common\components\AccessControl;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;

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
                        'index', 'rest'
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
        if (Yii::$app->user->isGuest) {
            return $this->render('index', [
                //  'dataProvider'  => $dataProvider,
            ]);
        } else {
            $posts = Post::find()
               // ->where(['>', 'id', 1])
                ->requiredFields(['id', 'user_id', 'content', 'mainImage', 'name'])
                ->requiredExtraFields(['ownerLastName'])
                ->asArray()
                ->all();
            $dataProvider = new ArrayDataProvider([
                'allModels' => $posts,
                'pagination' => [
                    'pageSize' => 2,
                ],
            ]);
            return $this->render('posts', [
                  'dataProvider'  => $dataProvider,
            ]);
        }
    }

}
