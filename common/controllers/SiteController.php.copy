<?php

namespace common\controllers;

use Yii;
use common\components\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use app\modules\adminxx\models\form\Login;

class SiteController extends Controller
{
    /**
     * @return array
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
                        'index', 'error'
                    ],
                    'roles'      => [
                        '@', '?'
                    ],
                ],
                [
                    'allow' => true,
                    'actions' => ['login', 'signup', 'signup-confirm' ],
                    'roles' => ['?'],
                ],
                [
                    'allow' => true,
                    'actions' => ['logout'],
                    'roles' => ['@'],
                ],

            ],
        ];
        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'logout' => ['post'],
            ],

        ];

        return $behaviors;
    }


    public function actionIndex()
    {
        return $this->render('index');
    }


    public function actionLogin()
    {
        //  $this->layout = '@app/views/layouts/commonLayout.php';
        $model = new Login();
        if ($model->load(\Yii::$app->getRequest()->post()) && $model->login()) {
            $query = 'SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode, "ONLY_FULL_GROUP_BY,", ""))';
            Yii::$app->db->createCommand($query)->execute();

            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->getUser()->logout();
        return $this->redirect('/site/index');
    }

    /**
     * +++ Регистрация нового пользователя с подтверждением Емейла
     * @return string
     */
    public function actionSignup()
    {
        $model = new Signup();
        if (\Yii::$app->getRequest()->isPost) {
            $data = \Yii::$app->getRequest()->post('Signup');
            $model->setAttributes($data);
            $model->first_name = $data['first_name'];
            $model->middle_name =  $data['middle_name'];
            $model->last_name =  $data['last_name'];

            if ($user = $model->signup(true)) {
                \Yii::$app->session->setFlash('success', \Yii::t('app', 'Check your email to confirm the registration'));
                return $this->goHome();
            } else {
                \Yii::$app->session->setFlash('error', \Yii::t('app', 'Ошибка отправки токена'));
            }
        }
        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * +++ Подтверждение регистрации по токену
     * @return string
     */
    public function actionSignupConfirm($token)
    {
        $signupService = new Signup();

        try{
            $signupService->confirmation($token);
            \Yii::$app->session->setFlash('success', \Yii::t('app', 'Регистрация успешно подтверждена'));
        } catch (\Exception $e){
            \Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->goHome();
    }



    public function actionError()
    {
       // $this->layout = '@app/views/layouts/commonLayout.php';

        $exception = \Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error',
                [
                    'exception' => $exception,
                     'message' => $exception->getMessage(),
                    ]);
        }
    }

}
