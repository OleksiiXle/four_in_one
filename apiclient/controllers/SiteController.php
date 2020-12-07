<?php
namespace app\controllers;

use app\modules\adminxx\models\UserM;
use common\helpers\Functions;
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
use yii\helpers\Url;

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
                        'index', 'error', 'auth'
                    ],
                    'roles'      => [
                        '@', '?'
                    ],
                ],
                [
                    'allow' => true,
                    'actions' => ['login', 'signup', 'signup-confirm', 'signup-to-api' ],
                    'roles' => ['?'],
                ],
                [
                    'allow' => true,
                    'actions' => ['logout', 'logout-from-api'],
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
                'class' => 'app\components\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    public function onAuthSuccess($client)
    {
        $attributes = $client->getUserAttributes();
        Functions::log('****************************************************');
        Functions::log('******************************onAuthSuccess   $client:');
        Functions::log($client);
        Functions::log('****************************************************');
        Functions::log('******************************onAuthSuccess   $client->getUserAttributes()');
        Functions::log($attributes);
        /*
         Из фейсбука прдет:
        ******************************onAuthSuccess   $client:
app\components\clients\Facebook#1
(
    [authUrl] => 'https://www.facebook.com/dialog/oauth'
    [tokenUrl] => 'https://graph.facebook.com/oauth/access_token'
    [apiBaseUrl] => 'https://graph.facebook.com'
    [scope] => 'email'
    [attributeNames] => [
        0 => 'name'
        1 => 'email'
    ]
    [autoRefreshAccessToken] => false
    [autoExchangeAccessToken] => false
    [clientAuthCodeUrl] => 'https://graph.facebook.com/oauth/client_code'
    [version] => '2.0'
    [clientId] => '483951645567912'
    [clientSecret] => 'bf5e46299b5277c73255282e811f33c0'
    [validateAuthState] => true
    [yii\authclient\BaseOAuth:_returnUrl] => null
    [yii\authclient\BaseOAuth:_accessToken] => yii\authclient\OAuthToken#2
    (
        [tokenParamKey] => 'access_token'
        [tokenSecretParamKey] => 'oauth_token_secret'
        [createTimestamp] => 1607247454
        [yii\authclient\OAuthToken:_expireDurationParamKey] => 'expires_in'
        [yii\authclient\OAuthToken:_params] => [
            'access_token' => 'EAAG4JsXsK6gBAPtQAjaLjfKMGTZBuIAHMCUDJzItV6HkVNcboLJQZBJn2hA0yabwpUIBbn4wqDAAkatCYDbfdoCgVQAooFFk29wcRoaane2nLfG1hksaRPSzrvEy5oKMvtDjeu49ymG32oASW8bLkq0OwgahceSZCT3SiKvFwZDZD'
            'token_type' => 'bearer'
            'expires_in' => 5180972
        ]
    )
    [yii\authclient\BaseOAuth:_signatureMethod] => []
    [yii\authclient\BaseClient:_id] => 'facebook'
    [yii\authclient\BaseClient:_name] => null
    [yii\authclient\BaseClient:_title] => null
    [yii\authclient\BaseClient:_userAttributes] => [
        'name' => 'Oleksii Xle'
        'email' => 'lokoko.xle@ukr.net'
        'id' => '2537274223260699'
    ]
    [yii\authclient\BaseClient:_normalizeUserAttributeMap] => []
    [yii\authclient\BaseClient:_viewOptions] => null
    [yii\authclient\BaseClient:_httpClient] => yii\httpclient\Client#3
    (
        [baseUrl] => 'https://graph.facebook.com'
        [formatters] => [
            'urlencoded' => yii\httpclient\UrlEncodedFormatter#4
            (
                [encodingType] => 1
                [charset] => null
            )
        ]
        [parsers] => [
            'json' => yii\httpclient\JsonParser#5
            (
                [asArray] => true
            )
        ]
        [requestConfig] => []
        [responseConfig] => []
        [contentLoggingMaxSize] => 2000
        [yii\httpclient\Client:_transport] => yii\httpclient\StreamTransport#6
        (
            [yii\base\Component:_events] => []
            [yii\base\Component:_eventWildcards] => []
            [yii\base\Component:_behaviors] => null
        )
        [yii\base\Component:_events] => []
        [yii\base\Component:_eventWildcards] => []
        [yii\base\Component:_behaviors] => []
    )
    [yii\authclient\BaseClient:_requestOptions] => []
    [yii\authclient\BaseClient:_stateStorage] => app\components\XapiStateStorage#7
    (
        [session] => yii\web\Session#8
        (
            [flashParam] => '__flash'
            [handler] => null
            [*:_forceRegenerateId] => null
            [yii\web\Session:_cookieParams] => [
                'httponly' => true
            ]
            [yii\web\Session:frozenSessionData] => null
            [yii\web\Session:_hasSessionId] => true
            [yii\base\Component:_events] => []
            [yii\base\Component:_eventWildcards] => []
            [yii\base\Component:_behaviors] => null
        )
        [yii\base\Component:_events] => []
        [yii\base\Component:_eventWildcards] => []
        [yii\base\Component:_behaviors] => null
    )
    [yii\base\Component:_events] => []
    [yii\base\Component:_eventWildcards] => []
    [yii\base\Component:_behaviors] => null
)
****************************************************
******************************onAuthSuccess   $client->getUserAttributes()
[
    'name' => 'Oleksii Xle'
    'email' => 'lokoko.xle@ukr.net'
    'id' => '2537274223260699'
]
         */

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

    public function actionLogin($mode = 'withoutSignup')
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

     //  Functions::logRequest();
        $this->layout = '@common/views/layouts/loginLayout.php';

        switch ($mode) {
            case 'withoutSignup':
                $model = new LoginForm();
                if ($model->load(Yii::$app->request->post())) {
                    if ($model->provider == 'xapi') {
                        if ($model->clientLogin()) {
                            return $this->goBack();
                        } else {
                            $model->password = '';
                            return $this->render('login', [
                                'model' => $model,
                            ]);
                        }
                    } else {
                        //-- facebok
                        $tmp = Url::toRoute(['auth', 'authclient' => 'facebook']);
                        return $this->redirect(Url::toRoute(['auth', 'authclient' => 'facebook']));
                    }

                } else {
                    $model->password = '';
                    return $this->render('login', [
                        'model' => $model,
                    ]);

                }
                break;
            case 'withSignup':
                $model = new SignupForm();
                if ($model->load(Yii::$app->request->post())) {
                    if ($model->getApiRegistration()) {
                        return $this->goHome();
                    }
                }

                return $this->render('signupToApi', [
                    'model' => $model,
                ]);
                break;
            default:
                throw new BadRequestHttpException('wrong mode');
        }
    }

    public function actionLogoutFromApi()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $this->layout = '@common/views/layouts/loginLayout.php';

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

    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->signup(false)) {
                return $this->goHome();
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    public function actionSignupToApi()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->signupToApi(false)) {
                return $this->goHome();
            }
        }

        return $this->render('signupToApi', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->getUser()->logout();
        return $this->redirect(Url::toRoute('/site/index'));
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
