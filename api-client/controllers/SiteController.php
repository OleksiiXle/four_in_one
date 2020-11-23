<?php
namespace app\controllers;

use app\modules\adminxx\models\UserM;
use Yii;
use app\components\AccessControl;
//use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\base\InvalidArgumentException;
use yii\httpclient\Client;
use yii\web\BadRequestHttpException;
//use app\modules\adminxx\models\form\Login;

use app\models\UserToken;
use app\models\Auth;
use app\models\LoginForm;
use app\models\LogoutForm;
use app\models\ResendVerificationEmailForm;
use app\models\VerifyEmailForm;
use app\models\PasswordResetRequestForm;
use app\models\ResetPasswordForm;
use app\models\SignupForm;

class SiteController extends Controller
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

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    public function onAuthSuccess($client)
    {
        $attributes = $client->getUserAttributes();

        /* @var $auth Auth */
        $auth = Auth::find()->where([
            'source' => $client->getId(),
            'source_id' => $attributes['id'],
        ])->one();

        if (Yii::$app->user->isGuest) {
            if ($auth) { // авторизация
                $user = $auth->user;
                Yii::$app->user->login($user);
            } else { // регистрация
                if (isset($attributes['email']) && User::find()->where(['email' => $attributes['email']])->exists()) {
                    Yii::$app->getSession()->setFlash('error', [
                        Yii::t('app', "Пользователь с такой электронной почтой как в {client} уже существует, но с ним не связан. Для начала войдите на сайт использую электронную почту, для того, что бы связать её.", ['client' => $client->getTitle()]),
                    ]);
                } else {
                    $password = Yii::$app->security->generateRandomString(6);
                    $user = new UserM([
                        'username' => $attributes['login'],
                        'email' => $attributes['email'],
                        'password' => $password,
                    ]);
                    $user->generateAuthKey();
                    $user->generatePasswordResetToken();
                    $transaction = $user->getDb()->beginTransaction();
                    if ($user->save()) {
                        $auth = new Auth([
                            'user_id' => $user->id,
                            'source' => $client->getId(),
                            'source_id' => (string)$attributes['id'],
                        ]);
                        if ($auth->save()) {
                            $transaction->commit();
                            Yii::$app->user->login($user);
                        } else {
                            print_r($auth->getErrors());
                        }
                    } else {
                        print_r($user->getErrors());
                    }
                }
            }
        } else { // Пользователь уже зарегистрирован
            if (!$auth) { // добавляем внешний сервис аутентификации
                $auth = new Auth([
                    'user_id' => Yii::$app->user->id,
                    'source' => $client->getId(),
                    'source_id' => $attributes['id'],
                ]);
                $auth->save();
            }
        }
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->clientLogin()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogoutFromApi()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LogoutForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->providersLogout()){
                return $this->goHome();
            }
        }
        return $this->render('logout', [
            'model' => $model,
        ]);
    }











    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLoginOld()
    {
        //  $this->layout = '@app/views/layouts/commonLayout.php';
        $model = new Login();
        if ($model->load(\Yii::$app->getRequest()->post()) && $model->login()) {

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
