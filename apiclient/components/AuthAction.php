<?php

namespace app\components;

use common\helpers\Functions;
use yii\authclient\OAuth2;
use yii\base\Action;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use Yii;

/**
 * AuthAction performs authentication via different auth clients.
 * It supports [[OpenId]], [[OAuth1]] and [[OAuth2]] client types.
 *
 * Usage:
 *
 * ```php
 * class SiteController extends Controller
 * {
 *     public function actions()
 *     {
 *         return [
 *             'auth' => [
 *                 'class' => 'yii\authclient\AuthAction',
 *                 'successCallback' => [$this, 'successCallback'],
 *             ],
 *         ]
 *     }
 *
 *     public function successCallback($client)
 *     {
 *         $attributes = $client->getUserAttributes();
 *         // user login or signup comes here
 *     }
 * }
 * ```
 *
 * Usually authentication via external services is performed inside the popup window.
 * This action handles the redirection and closing of popup window correctly.
 *
 * @see Collection
 * @see \yii\authclient\widgets\AuthChoice
 *
 * @property string $cancelUrl Cancel URL.
 * @property string $successUrl Successful URL.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class AuthAction extends Action
{
    /**
     * @var string name of the auth client collection application component.
     * It should point to [[Collection]] instance.
     */
    public $clientCollection = 'authClientCollection';
    /**
     * @var string name of the GET param, which is used to passed auth client id to this action.
     * Note: watch for the naming, make sure you do not choose name used in some auth protocol.
     */
    public $clientIdGetParamName = 'authclient';
    /**
     * @var callable PHP callback, which should be triggered in case of successful authentication.
     * This callback should accept [[ClientInterface]] instance as an argument.
     * For example:
     *
     * ```php
     * public function onAuthSuccess($client)
     * {
     *     $attributes = $client->getUserAttributes();
     *     // user login or signup comes here
     * }
     * ```
     *
     * If this callback returns [[Response]] instance, it will be used as action response,
     * otherwise redirection to [[successUrl]] will be performed.
     *
     */
    public $successCallback;

    /**
     * @var callable PHP callback, which should be triggered in case of canceled authentication.
     * This callback should accept [[ClientInterface]] instance as an argument.
     */
    public $cancelCallback;
    /**
     * @var string name or alias of the view file, which should be rendered in order to perform redirection.
     * If not set - default one will be used.
     */
    public $redirectView;

    /**
     * @var string the redirect url after successful authorization.
     */
    private $_successUrl = '';
    /**
     * @var string the redirect url after unsuccessful authorization (e.g. user canceled).
     */
    private $_cancelUrl = '';


    /**
     * @param string $url successful URL.
     */
    public function setSuccessUrl($url)
    {
        $this->_successUrl = $url;
    }

    /**
     * @return string successful URL.
     */
    public function getSuccessUrl()
    {
        if (empty($this->_successUrl)) {
            $this->_successUrl = $this->defaultSuccessUrl();
        }

        return $this->_successUrl;
    }

    /**
     * @param string $url cancel URL.
     */
    public function setCancelUrl($url)
    {
        $this->_cancelUrl = $url;
    }

    /**
     * @return string cancel URL.
     */
    public function getCancelUrl()
    {
        if (empty($this->_cancelUrl)) {
            $this->_cancelUrl = $this->defaultCancelUrl();
        }

        return $this->_cancelUrl;
    }

    /**
     * Creates default [[successUrl]] value.
     * @return string success URL value.
     */
    protected function defaultSuccessUrl()
    {
        return Yii::$app->getUser()->getReturnUrl();
    }

    /**
     * Creates default [[cancelUrl]] value.
     * @return string cancel URL value.
     */
    protected function defaultCancelUrl()
    {
        return Url::to(Yii::$app->getUser()->loginUrl);
    }

    /**
     * Runs the action.
     */
    public function run()
    {
        $debug = false;
        Functions::log('CLIENT --- ***************** AuthAction run');
        Functions::log("CLIENT --- app\components\\AuthAction\\public function run():");
        if (empty($_GET[$this->clientIdGetParamName])) {
            $clientId = 'diya';
            $debug = true;
        } else {
            $clientId = $_GET[$this->clientIdGetParamName];
        }
        Functions::log("CLIENT --- clientId = $clientId");
        /* @var $collection \yii\authclient\Collection */
        $collection = Yii::$app->get($this->clientCollection);
        $client = $collection->getClient($clientId);
        if ($debug) {
            $client->debug = $debug;
        }

        if ($clientId == 'Iit') {
            $client->debug = true;
            return $this->authOAuth2Iit($client);
        }

        if ($client instanceof OAuth2) {
            return $this->authOAuth2($client);
        }

        throw new NotSupportedException('Provider "' . get_class($client) . '" is not supported.');
    }

    protected function authOAuth2Iit($client)
    {
        Functions::log("CLIENT --- authOAuth2Iit:", true);
        Functions::logRequest();
        if (isset($_GET['error'])) {
            if ($_GET['error'] == 'access_denied') {
                Functions::log('CLIENT --- *** access_denied');
                return $this->redirectCancel();
            } else {
                if (isset($_GET['error_description'])) {
                    $errorMessage = $_GET['error_description'];
                } elseif (isset($_GET['error_message'])) {
                    $errorMessage = $_GET['error_message'];
                } else {
                    $errorMessage = http_build_query($_GET);
                }
                Functions::log('CLIENT --- *** errorMessage:');
                Functions::log($errorMessage);
                throw new Exception('Auth error: ' . $errorMessage);
            }
        }

        // Get the access_token and save them to the session.
        if (isset($_GET['code'])) {
            if ($client->debug) {
                throw new Exception('has code = ' . $_GET['code']);
            }

            $code = (isset($_GET['code'])) ? $_GET['code'] : 'debug';
            Functions::log("CLIENT --- !!!!!! пришел code=$code");
            Functions::log("CLIENT --- пытаемся извлечь AccessToken... ");
            Functions::log("CLIENT --- вернулись в protected function authOAuth2(client)");

            if ($client->fetchAccessToken($code)) {
                Functions::log("CLIENT --- если получилось извлечь токен - выполняем свой метод onAuthSuccess ...");
                return $this->authSuccess($client);
            } else {
                return $this->authCancel($client);
            }
        } else {
            Functions::log("CLIENT --- buildAuthUrl : ");
            $url = $client->buildAuthUrl();
            Functions::log("CLIENT --- authUrl : $url");
            Functions::log("CLIENT --- rediretc to authUrl...");
            Functions::log("CLIENT --- ***********************************************************************");
            /*
             https://id.gov.ua/
            ?response_type=code
            &client_id=3d0430da5e80f50cd7dad45f8e7adf2c
            &auth_type=dig_sign
            &state=fc4228705f387da5992abb890a75c4dd2657498a6565f5588fce40e1722c6b59
            &redirect_uri=http%3A%2F%2F192.168.33.11%2Fdstest%2Fapiclient%2Fsite%2Fauth
             */
            if ($client->debug) {
              //  throw new Exception('redirect to ' . $url);
            }
            return Yii::$app->getResponse()->redirect($url);
        }
    }


    /**
     * Performs OAuth2 auth flow.
     * @param OAuth2 $client auth client instance.
     * @return Response action response.
     * @throws \yii\base\Exception on failure.
     */
    protected function authOAuth2($client)
    {
        Functions::log("CLIENT --- app\components\\AuthAction\\protected function authOAuth2(client):", true);
        Functions::log("CLIENT --- client class = " . get_class($client));
        Functions::logRequest();
        //  Functions::log('CLIENT --- *** $_GET:');
        //  Functions::log($_GET);
        if (isset($_GET['error'])) {
            if ($_GET['error'] == 'access_denied') {
                Functions::log('CLIENT --- *** access_denied');
                // user denied error
                return $this->redirectCancel();
            } else {
                // request error
                if (isset($_GET['error_description'])) {
                    $errorMessage = $_GET['error_description'];
                } elseif (isset($_GET['error_message'])) {
                    $errorMessage = $_GET['error_message'];
                } else {
                    $errorMessage = http_build_query($_GET);
                }
                Functions::log('CLIENT --- *** errorMessage:');
                Functions::log($errorMessage);
                throw new Exception('Auth error: ' . $errorMessage);
            }
        }

        // Get the access_token and save them to the session.
        if (isset($_GET['code']) || (isset($client->debug) && $client->debug)) {

            $code = (isset($_GET['code'])) ? $_GET['code'] : 'debug';
            Functions::log("CLIENT --- !!!!!! пришел code=$code");
            Functions::log("CLIENT --- пытаемся извлечь AccessToken... ");
            Functions::log("CLIENT --- вернулись в protected function authOAuth2(client)");

            if ($client->fetchAccessToken($code)) {
                Functions::log("CLIENT --- если получилось извлечь токен - выполняем свой метод onAuthSuccess ...");
                return $this->authSuccess($client);
            } else {
                return $this->authCancel($client);
            }
        } else {
            Functions::log("CLIENT --- buildAuthUrl : ");
            $url = $client->buildAuthUrl();
            Functions::log("CLIENT --- authUrl : $url");
            Functions::log("CLIENT --- rediretc to authUrl...");
            Functions::log("CLIENT --- ***********************************************************************");
            /*
             https://id.gov.ua/
            ?response_type=code
            &client_id=3d0430da5e80f50cd7dad45f8e7adf2c
            &auth_type=dig_sign
            &state=fc4228705f387da5992abb890a75c4dd2657498a6565f5588fce40e1722c6b59
            &redirect_uri=http%3A%2F%2F192.168.33.11%2Fdstest%2Fapiclient%2Fsite%2Fauth
             */
            return Yii::$app->getResponse()->redirect($url);
        }
    }

    /**
     * This method is invoked in case of successful authentication via auth client.
     * @param ClientInterface $client auth client instance.
     * @throws InvalidConfigException on invalid success callback.
     * @return Response response instance.
     */
    protected function authSuccess($client)
    {
        if (!is_callable($this->successCallback)) {
            throw new InvalidConfigException('"' . get_class($this) . '::successCallback" should be a valid callback.');
        }
        call_user_func($this->successCallback, $client);
        /*
        $response = call_user_func($this->successCallback, $client);
        if ($response instanceof Response) {
            return $response;
        }
        return $this->redirectSuccess();
        */
    }

    protected function authCancel($client)
    {
        if (!is_callable($this->cancelCallback)) {
            throw new InvalidConfigException('"' . get_class($this) . '::cancelCallback" should be a valid callback.');
        }
        call_user_func($this->cancelCallback, $client);
        /*
        $response = call_user_func($this->successCallback, $client);
        if ($response instanceof Response) {
            return $response;
        }
        return $this->redirectSuccess();
        */
    }

    /**
     * Redirect to the given URL or simply close the popup window.
     * @param mixed $url URL to redirect, could be a string or array config to generate a valid URL.
     * @param bool $enforceRedirect indicates if redirect should be performed even in case of popup window.
     * @return \yii\web\Response response instance.
     */
    public function redirect($url, $enforceRedirect = true)
    {
        $viewFile = $this->redirectView;
        if ($viewFile === null) {
            $viewFile = __DIR__ . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'redirect.php';
        } else {
            $viewFile = Yii::getAlias($viewFile);
        }
        $viewData = [
            'url' => $url,
            'enforceRedirect' => $enforceRedirect,
        ];
        $response = Yii::$app->getResponse();
        $response->content = Yii::$app->getView()->renderFile($viewFile, $viewData);
        return $response;
    }

    /**
     * Redirect to the URL. If URL is null, [[successUrl]] will be used.
     * @param string $url URL to redirect.
     * @return \yii\web\Response response instance.
     */
    public function redirectSuccess($url = null)
    {
        if ($url === null) {
            $url = $this->getSuccessUrl();
        }
        return $this->redirect($url);
    }

    /**
     * Redirect to the [[cancelUrl]] or simply close the popup window.
     * @param string $url URL to redirect.
     * @return \yii\web\Response response instance.
     */
    public function redirectCancel($url = null)
    {
        if ($url === null) {
            $url = $this->getCancelUrl();
        }
        return $this->redirect($url, false);
    }

}
