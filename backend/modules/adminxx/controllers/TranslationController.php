<?php

namespace backend\modules\adminxx\controllers;

use backend\controllers\MainController;
use common\components\conservation\ActiveDataProviderConserve;
use common\components\models\Translation;
use common\components\AccessControl;
use yii\helpers\FileHelper;
use backend\modules\adminxx\models\filters\TranslationFilter;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\VerbFilter;

class TranslationController extends MainController
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
                         'index', 'create', 'update', 'delete', 'delete-translations', 'upload'
                    ],
                    'roles'      => ['adminTranslateUpdate' ],
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                         'change-language',
                    ],
                    'roles'      => ['@' , '?' ],
                ],
            ],
                /*
            'denyCallback' => function ($rule, $action) {
                \yii::$app->getSession()->addFlash("warning",\Yii::t('app', "Действие запрещено"));
                return $this->redirect(\Yii::$app->request->referrer);

        }
        */
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'delete' => ['post'],
                'delete-translations' => ['post'],
            ],

        ];
        return $behaviors;
    }

    /**
     * +++ Список всех
     * @return mixed
     */
    public function actionIndex()
    {
     //   $r = Translation::getDictionary('app', 'ru-RU');
        /*
            $dataProvider = new ActiveDataProvider([
                'query' => Translation::find()
                ->where(['language' => \Yii::$app->language])
                ,
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]);
            */
        $dataProvider = new ActiveDataProviderConserve([
            // 'searchId' => $id,
            'filterModelClass' => TranslationFilter::class,
            'conserveName' => 'translationGrid',
            'pageSize' => 10,
            /*
            'sort' => ['attributes' => [
                'id',
                'username',
                'nameFam' => [
                    'asc' => [
                        'user_data.last_name' => SORT_ASC,
                    ],
                    'desc' => [
                        'user_data.last_name' => SORT_DESC,
                    ],
                ],
                'lastRoutTime' => [
                    'asc' => [
                        'user_data.last_rout_time' => SORT_ASC,
                    ],
                    'desc' => [
                        'user_data.last_rout_time' => SORT_DESC,
                    ],
                ],
                'lastRout' => [
                    'asc' => [
                        'user_data.last_rout' => SORT_ASC,
                    ],
                    'desc' => [
                        'user_data.last_rout' => SORT_DESC,
                    ],
                ],
                'status' => [
                    'asc' => [
                        'user.status' => SORT_ASC,
                    ],
                    'desc' => [
                        'user.status' => SORT_DESC,
                    ],
                ],
            ]],
            */

        ]);

        return $this->render('index',[
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * +++ Регистрация нового
     * @return string
     */
    public function actionCreate()
    {
        $dataForAutocompleteRu = Translation::getDataForAutocomplete('ru-RU', 'app');
        $dataForAutocompleteEn = Translation::getDataForAutocomplete('en-US', 'app');
        $dataForAutocompleteUk = Translation::getDataForAutocomplete('uk-UK', 'app');

        $model = new Translation();
        $model->category = 'app';
        if (\Yii::$app->getRequest()->isPost) {
            $data = \Yii::$app->getRequest()->post('Translation');
            if (isset($data['reset-button'])){
                return $this->redirect(['index']);
            }
            $model->setAttributes($data);
            if ($model->saveTranslation()) {
                $session = \Yii::$app->session;
                if ($session->get('searchIid')){
                    $session->remove('searchIid');
                }
                $session->set('searchIid', $model->id );

                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'dataForAutocompleteRu' => $dataForAutocompleteRu,
            'dataForAutocompleteEn' => $dataForAutocompleteEn,
            'dataForAutocompleteUk' => $dataForAutocompleteUk,
        ]);
    }

    /**
     * +++ Изменение старого
     * @return string
     */
    public function actionUpdate($id)
    {
        $model = Translation::findOne($id);
        $model->setLanguages();
        if (\Yii::$app->getRequest()->isPost) {
            $data = \Yii::$app->getRequest()->post('Translation');
            if (isset($data['reset-button'])){
                return $this->redirect(['index']);
            }
            $model->setAttributes($data);
            if ($model->saveTranslation()) {
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * +++ Удаление
     * @return string
     */
    public function actionDelete($tkey)
    {
        $tmp = 1;
        if (\Yii::$app->request->isPost){
            $userDel = Translation::deleteAll(['tkey' => $tkey]);
            if ($userDel === 0){
                \yii::$app->getSession()->addFlash("warning","Ошибка при удалении.");
            }
        }
        return $this->redirect('index');

    }

    public function actionChangeLanguage()
    {
        try {
            $language    = \Yii::$app->getRequest()->get('language');
            if (!empty($language)){
                \Yii::$app->userProfile->language = $language;
            }
        } catch (\Exception $e) {
            $this->result['data'] = $e->getMessage();
        }
        return $this->redirect(\Yii::$app->request->referrer);
    }

    public function actionDeleteTranslations()
    {
        $_post = \Yii::$app->request->post();
        if (isset($_post['checkedIds'])) {
            $checkedIds = $_post['checkedIds'];
            $tkeys = (new Query())
                ->select('tkey')
                ->from(Translation::tableName())
                ->where(['IN', 'id', $checkedIds])
                ->indexBy('tkey')
                ->all();
            $translationsToDelete = Translation::deleteAll(['IN', 'tkey', array_keys($tkeys)]);
            $this->result = [
                'status' => true,
                'data' => $translationsToDelete
            ];
        }

        return $this->asJson($this->result);
    }

    public function actionUpload()
    {
        $fileName = Translation::upload();
        $options['mimeType'] = FileHelper::getMimeTypeByExtension($fileName);
        $attachmentName = basename($fileName);
        \Yii::$app->response->sendFile($fileName, $attachmentName, $options);
    }
}