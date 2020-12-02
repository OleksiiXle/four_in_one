<?php

namespace frontend\modules\post\controllers;

use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use common\components\conservation\ActiveDataProviderConserve;
use common\models\FileUpload;
use common\components\AccessControl;
use frontend\modules\post\models\filters\PostFilter;
use frontend\modules\post\models\Post;
use frontend\modules\post\models\PostMedia;

class PostController extends MainController
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
                        'index',
                    ],
                    'roles'      => [ '@', '?'],
                ],
                [
                    'allow' => true,
                    'actions' => ['create', 'update', 'delete', 'get-media-preview', 'get-post-media',
                        'create-post-media', 'delete-post-media', 'test'],
                    'roles' => ['@'],
                ],
            ],
            /*
            'denyCallback' => function ($rule, $action) {
                if (\Yii::$app->user->isGuest){
                    $redirect = Url::to(\Yii::$app->user->loginUrl);
                    return $this->redirect( $redirect);
                } else {
                    \yii::$app->getSession()->addFlash("warning",\Yii::t('app', "Действие запрещено"));
                    return $this->redirect(\Yii::$app->request->referrer);
                }
            }
            */
        ];
        return $behaviors;
    }

    public function actionTest()
    {
        return $this->render('test');
    }

    /**
     * Список постов
     * @return string
     */
    public function actionIndex() {
        $dataProvider = new ActiveDataProviderConserve([
            'filterModelClass' => PostFilter::class,
            'conserveName' => 'postGrid',
            'pageSize' => 15,
        ]);
        return $this->render('index',[
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Создать новый пост
     * @return string
     */
    public function actionCreate()
    {
        $model = new Post();
        $model->user_id = \Yii::$app->user->id;
        if (\Yii::$app->request->isPost){
            $_post = \Yii::$app->request->post();
            if (isset($_post['submit-button'])){
                $model->setAttributes($_post['Post']);
                if ($model->savePost()){
                    return $this->redirect(Url::to(['update', 'id' => $model->id]));
                }
            }
        }
        return $this->render('update', ['model' => $model]);
    }

    /**
     * Редактировать пост
     * @return string
     */
    public function actionUpdate($id)
    {
      //  return $this->render('test');
        $model = Post::findOne($id);
        $modelMedia = new PostMedia();
        $modelMedia->post_id = $id;
        $modelMedia->type = PostMedia::TYPE_IMAGE;
        $modelMediaProvider = new ActiveDataProvider([
           'query' => PostMedia::find()
               ->where(['post_id' => $id]),
            'pagination' => [
                'pageSize' => 3,
                ],
            ]);
        if (isset($model)){
            if (\Yii::$app->request->isPost){
                $_post = \Yii::$app->request->post();
                if (isset($_post['signup-button'])){
                    $model->setAttributes($_post['Post']);
                    if ($model->savePost()){
                        return $this->redirect('index');
                    }
                } else {
                    return $this->redirect('index');

                }

            }
            return $this->render('update', [
                'model' => $model,
                'modelMedia' => $modelMedia,
                'modelMediaProvider' => $modelMediaProvider,
                ]);
        } else {
            \yii::$app->getSession()->addFlash("warning",\Yii::t('app', "Информация не найдена"));
            return $this->redirect('index');
        }
    }

    /**
     * Удалить пост
     * @return string
     */
    public function actionDelete($id)
    {
        $r=1;
        if (\Yii::$app->request->isPost){
            $model = Post::findOne($id);
            if ($model->deletePost()){
                \yii::$app->getSession()->addFlash("success",\Yii::t('app', "ok"));
            } else {
                \yii::$app->getSession()->addFlash("warning",\Yii::t('app', "Ошибка удаления поста"));
            }
        }
        return $this->redirect('index');

    }

    /**
     * AJAX Удалить пост
     * @return string
     */
    public function actionDeletePostMedia()
    {
        $r=1;
        if ($id = \Yii::$app->request->post('postMediaId')) {
            $model = PostMedia::findOne($id);
            if (isset($model)){
                if ($model->deletePostMedia()){
                    $this->result=[
                        'status' => true,
                        'data' => 'ok',
                        ];
                } else {
                    $this->result['data'] = $model->getErrors();
                }
            }
        }
        return $this->asJson($this->result);

    }

    /**
     * AJAX загрузка превью изображения при редактировании
     * @return string
     */
    public function actionGetMediaPreview()
    {
        $userId = \Yii::$app->user->id;

        if ($type = \Yii::$app->request->post('type')) {
            $model = new FileUpload();
            if ($model->getInstance($type)){
                if ($model->uploadToTmp($userId)){
                    $this->result = [
                        'status' =>true,
                        'data' => [
                            'fileName' => $model->name,
                            'webFullFileName' => $model->webFullFileName,
                        ]
                    ];
                } else {
                    $this->result['data'] = $model->getErrors();
                }
            } else {
                $this->result['data'] = $model->getErrors();
            }
        }
        return $this->asJson($this->result);
    }

    /**
     * AJAX загрузка списка изображений при редактировании
     * @return string
     */
    public function actionGetPostMedia($id)
    {
        $t=1;
        $postMedia = PostMedia::find()
            ->where(['post_id' => $id])
            ->all();
        return $this->renderAjax('_postMedia',['postMedia' => $postMedia]);
    }


    /**
     * AJAX созранение нового медиа
     */
    public function actionCreatePostMedia()
    {
        $_post = \Yii::$app->request->post();
        if (isset($_post) && isset($_post['PostMedia'])){
            $model = new PostMedia();
            $model->setAttributes($_post['PostMedia']);
            if ($model->savePostMedia(\Yii::$app->user->id)){
                $this->result = [
                    'status' => true,
                    'data' => $model->id,
                ];
            } else {
                $this->result['data'] = $model->getErrors();
            }

        }
        return $this->asJson($this->result);
    }




}
