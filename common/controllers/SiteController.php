<?php
namespace common\controllers;

use common\models\form\ChangePassword;
use common\models\form\PasswordResetRequestForm;
use common\models\form\ResetPasswordForm;
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
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'signup', 'signup-email', 'verify-email', 'error',
                            'request-password-reset', 'reset-password'],
                        'allow' => true,
                        'roles' => ['?'],

                    ],
                    [
                        'actions' => ['logout', 'change-password', 'deny-access'],
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

    /**
     * Change password
     * @return string
     */
    public function actionChangePassword()
    {
        $model = new ChangePassword();
        if (Yii::$app->request->isPost) {
            $_post = Yii::$app->request->post();
            if (!isset($_post['reset-btn'])) {
                if ($model->load($_post) && $model->change()) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Пароль успешно изменен'));
                    return $this->goHome();
                }
            } else {
                return $this->goBack();
            }
        }

        return $this->render('@common/views/site/changePassword', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('@common/views/site/requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        $tmp = 1;
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->change()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('@common/views/site/resetPassword', [
            'model' => $model,
        ]);
    }



    public function actionDenyAccess()
    {
        return $this->render('@common/views/site/denyAccess');
    }

}
