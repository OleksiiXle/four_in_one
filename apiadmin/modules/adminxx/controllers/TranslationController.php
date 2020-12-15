<?php

namespace apiadmin\modules\adminxx\controllers;

use Yii;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\Response;
use common\components\conservation\ActiveDataProviderConserve;
use common\components\models\Translation;
use common\components\AccessControl;
use apiadmin\controllers\MainController;
use apiadmin\modules\adminxx\grids\TranslationGrid;

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
        $grid = new TranslationGrid();
        if (Yii::$app->request->isPost) {
            Yii::$app->getResponse()->format = Response::FORMAT_HTML;
            return $grid->reload(Yii::$app->request->post());
        }
        return $this->render('index', [
            'grid' => $grid,
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
                return $this->redirect(Url::toRoute('index'));
            }
            $model->setAttributes($data);
            if ($model->saveTranslation()) {
                $session = \Yii::$app->session;
                if ($session->get('searchIid')){
                    $session->remove('searchIid');
                }
                $session->set('searchIid', $model->id );

                return $this->redirect(Url::toRoute('index'));
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
                return $this->redirect(Url::toRoute('index'));
            }
            $model->setAttributes($data);
            if ($model->saveTranslation()) {
                return $this->redirect(Url::toRoute('index'));
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
        return $this->redirect(Url::toRoute('index'));

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