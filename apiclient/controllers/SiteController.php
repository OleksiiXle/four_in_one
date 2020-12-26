<?php
namespace app\controllers;

use app\models\form\LoginWithoutApi;
use common\models\UserM;
use common\helpers\Functions;
use Yii;
use common\components\AccessControl;
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
                        'index', 'error', 'auth', 'deny-access'
                    ],
                    'roles'      => [
                        '@', '?'
                    ],
                ],
                [
                    'allow' => true,
                    'actions' => ['login', 'signup', 'signup-confirm', 'signup-to-api', 'login-without-api' ],
                    'roles' => ['?'],
                ],
                [
                    'allow' => true,
                    'actions' => ['logout', 'logout-from-api', 'after-api-login'],
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
            //    'successUrl' => Url::toRoute('after-api-login'),
           //     'redirectView' => '@app/views/site/afterApiLogin.php',
            ],
        ];
    }

    public function actionAfterApiLogin()
    {
        $tmp = 1;
        $this->layout = '@app/modules/post/views/layouts/postLayout.php';
        return $this->render('afterApiLogin', [
          //  'provider' => $provider,
         //   'data' => $data,
        ]);
    }

    public function onAuthSuccess($client)
    {
        Functions::log("CLIENT --- app\controllers\\ class SiteController extends Controller", true);
        Functions::log("CLIENT ---  public function onAuthSuccess(client)");
        Functions::log('CLIENT --- ******************************onAuthSuccess   $client:');
        Functions::log($client);
        Functions::log('CLIENT --- пытаемся получить   $client->getUserAttributes()');
        $attributes = $client->getUserAttributes();
        Functions::log('CLIENT --- $client->getUserAttributes():');
        Functions::log($attributes);
        $resultMessage = 'Авторизация подтверждена, сервис ' . $client->name . ' вернул Ваши данные:';
        foreach ($attributes as $name => $value) {
            $resultMessage .= '<br>' . $name . ' = ';
            if (!is_array($value)) {
                $resultMessage .= $value;
            } else {
                $buff = '';
                foreach ($value as $n => $v)  {
                    $buff .= "<br>----------->$n";
                }
                $resultMessage .= $buff;
            }
        }
        Yii::$app->session->setFlash('success', $resultMessage);
        return $this->redirect('after-api-login');
     //   Yii::$app->session->setFlash('success', \yii\helpers\VarDumper::dumpAsString($attributes) );
    //    return $this->redirect(['after-api-login', 'provider' => $client->name,
    //        'data' => $attributes, ]);

/*
     'name' => 'Oleksii Xle'
    'email' => 'lokoko.xle@ukr.net'
    'id' => '2537274223260699'

 */
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
    }

    public function actionLogin($mode = 'withoutSignup')
    {
        $this->layout = '@common/views/layouts/loginLayout.php';
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

     //  Functions::logRequest();
        // https://672327fb4a6e.ngrok.io
        // https://672327fb4a6e.ngrok.io/dstest/apiclient
        // https://672327fb4a6e.ngrok.io/dstest/apiclient/site/auth?authclient=facebook

        switch ($mode) {
            case 'withoutSignup':
                $model = new LoginForm();
                if (Yii::$app->request->isPost) {
                    //-- login without api
                    if ($model->load(Yii::$app->request->post()) && $model->login()){
                        if ($model->provider != 'none') {
                            return $this->redirect(Url::toRoute(['auth', 'authclient' => $model->provider]));
                        } else {
                            Yii::$app->session->setFlash('success', 'Подключения к АПИ нет');
                            return $this->goBack();
                        }
                    }
                }

                $model->password = '';
                return $this->render('login', [
                    'model' => $model,
                ]);
/*
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
                        if (Yii::$app->request->isPost) {
                            $model = new LoginWithoutApi();
                            $_post = Yii::$app->request->post();
                            $model->setAttributes($_post['LoginForm']);
                            if ($model->login()) {
                                $tmp = Url::toRoute(['auth', 'authclient' => 'facebook']);
                                return $this->redirect(Url::toRoute(['auth', 'authclient' => 'facebook']));
                                //return $this->goBack();
                            } else {
                                return $this->render('signupToApi', [
                                    'model' => $model,
                                ]);

                            }
                        } else {

                        }
                        //-- facebok
                        if ($model->load(\Yii::$app->getRequest()->post()) && $model->login()) {
                            $tmp = Url::toRoute(['auth', 'authclient' => 'facebook']);
                            return $this->redirect(Url::toRoute(['auth', 'authclient' => 'facebook']));
                            //return $this->goBack();
                        } else {
                            return $this->render('signupToApi', [
                                'model' => $model,
                            ]);

                        }
                    }

                } else {
                    $model->password = '';
                    return $this->render('login', [
                        'model' => $model,
                    ]);

                }
*/
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

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLoginWithoutApi()
    {
        $this->layout = '@common/views/layouts/loginLayout.php';
        //   $this->layout = false;

        $model = new LoginWithoutApi();
        if ($model->load(\Yii::$app->getRequest()->post()) && $model->login()) {
            return $this->goBack();
        } else {
            //    return $this->render('login', [
            return $this->render('@common/views/site/login', [
                'model' => $model,
            ]);
        }
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
        return $this->goHome();
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

    public function actionDenyAccess()
    {
        return $this->render('denyAccess');
    }
}
