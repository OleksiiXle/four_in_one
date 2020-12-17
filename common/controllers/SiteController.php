<?php
namespace common\controllers;

use Yii;
use yii\filters\VerbFilter;
use common\components\AccessControl;
use common\models\form\Login;
use common\models\form\Signup;
use yii\helpers\Url;

/**
 * Site controller
 */
class SiteController extends MainController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'signup', 'signup-email', 'verify-email', 'error'],
                        'allow' => true,
                        'roles' => ['?'],

                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [ 'index'],
                        'allow' => true,
                        'roles' => ['@', '?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        $this->layout = '@common/views/layouts/loginLayout.php';
        //   $this->layout = false;

        $model = new Login();
        if ($model->load(\Yii::$app->getRequest()->post()) && $model->login()) {
            return $this->goBack();
        } else {
        //    return $this->render('login', [
            return $this->render('@common/views/site/login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->userProfile->language = Yii::$app->language;

        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $this->layout = '@common/views/layouts/loginLayout.php';
        $model = new Signup();
        $model->scenario = Signup::SCENARIO_SIGNUP_BY_HIMSELF;
        if (Yii::$app->request->isPost) {
            $_post = Yii::$app->request->post();
            if (!isset($_post['reset-btn'])) {
                if ($model->load($_post) && $model->signup(false)) {
                    return $this->redirect(Url::toRoute('/site/login'));
                }
            } else {
                return $this->goBack();
            }
        }

        return $this->render('@common/views/site/signup', [
            'model' => $model,
        ]);
    }

    /**
     * Signs user up with email confirmation.
     *
     * @return mixed
     */
    public function actionSignupEmail()
    {
        $this->layout = '@common/views/layouts/loginLayout.php';
        $model = new Signup();
        $model->scenario = Signup::SCENARIO_SIGNUP_BY_HIMSELF_WITH_CONFIRMATION;
        if (Yii::$app->request->isPost) {
            $_post = Yii::$app->request->post();
            if (!isset($_post['reset-btn'])) {
                if ($model->load($_post) && $model->signup(true)) {
                    Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
                    return $this->goHome(); }
            } else {
                return $this->goBack();
            }
        }

        return $this->render('@common/views/site/signup', [
            'model' => $model,
        ]);
    }

    /**
     * +++ Подтверждение регистрации по токену
     * @return string
     */
    public function actionVerifyEmail($token)
    {
        $signupService = new Signup();
        try{
            if ($signupService->confirmation($token)) {
                \Yii::$app->session->setFlash('success', \Yii::t('app', 'Регистрация успешно подтверждена'));
            }
        } catch (\Exception $e){
            \Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->goHome();
    }
}
