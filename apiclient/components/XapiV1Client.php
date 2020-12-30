<?php
namespace app\components;

use Yii;
use yii\base\Component;
use yii\di\Instance;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\helpers\VarDumper;


class XapiV1Client extends Component {

    /** Handler result consts */
    const RETURN_SUCCESS          = 1;
    const RETURN_VALIDATOR_ERRORS = 2;
    const RETURN_EXCEPT           = 4;
    const RETURN_NONSENS          = 8;
    const RETURN_AUTH_ERROR       = 16;
    const RETURN_PERMS_ERROR      = 32;

    /** Message types */
    const MESSAGE_SUCCESS = "success";
    const MESSAGE_INFO    = "info";
    const MESSAGE_WARNING = "warning";
    const MESSAGE_ERROR   = "error";

    /** Ajax header message */
    const AJAX_MESSAGE_HEADER_NAME = 'x-response-message';

    public $ajaxResponse = false;
    public $ajaxResult;

    /** Handler results params */
    public $responseStatus = 0;
    public $request;
    public $response;
    public $model;

    /** Success */
    public $successMessage;
    public $successMessageType = "success";
    public $successCallback;
    public $successRedirect;

    /** Valid */
    public $invalidMessage;
    public $invalidMessageType = "warning";
    public $invalidCallback;
    public $invalidRedirect;

    /** Exception */
    public $exceptionMessage;
    public $exceptionMessageType = "error";
    public $exceptionCallback;
    public $exceptionRedirect;
    public $useDefExceptMessage  = true;

    /** Nonsens */
    public $nonsensMessage;
    public $nonsensMessageType   = "error";
    public $nonsensCallback;
    public $nonsensRedirect;
    public $useDefNonsensMessage = false;

    /** Auth */
    public $authMessage;
    public $authMessageType   = "error";
    public $authCallback;
    public $authRedirect      = 'apiclient/site/login';
    public $useDefAuthMessage = true;

    /** Permission error */
    public $permsMessage;
    public $permsMessageType   = "error";
    public $permsCallback;
    public $permsRedirect;
    public $useDefPermsMessage = true;

    /** Default messages */
    protected $defName;
    protected $defMessage;

    /** No success */
    public $noSuccessRedirect;
    public $noSuccessMessage;
    public $noSuccessMessageType = "error";
    public $noSuccessCallback;

    /**
     * @return mixed
     */
    public function getApiBaseUrl()
    {
        if ($this->_apiBaseUrl === null) {
            $this->_apiBaseUrl = Yii::$app->authClientCollection->getClientInfo('xapi')['api_base_url'];
        }
        return $this->_apiBaseUrl;
    }

    /** Xapiapi */
    private $_apiBaseUrl = null;
    protected $_httpClient = 'yii\httpclient\Client';

    public function setHandlerParams($params) {
        try {
            foreach ($params as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        } catch (\Exception $ex) {
            \yii::trace(VarDumper::dumpAsString($ex), "upzapi");
        }
    }

    public function userRegister($params, $data) {
        $ret = $this->callMethod('/user/register', $params, 'POST', $data);
        return $ret;
    }

    public function userView($id, $expand = false) {
        $params = ['id' => $id];
        if ($expand) {
            $params['expand']  = 'email';
            $params['nocheck'] = true;
        }
        return $this->callMethod('/user/view', $params);
    }

    public function userList($expand = false) {
        $params = [];
        if ($expand) {
            $params['expand'] = 'email';
        }
        return $this->callMethod('/user', $params);
    }

    public function userCompanies($userId) {
        $params = ['user_id' => $userId];
        return $this->callMethod('/user/companies', $params);
    }

    /**
     * Создаёт запрос к API серверу, получает ответ и обрабатывает его.
     * Возвращает результат обработки запроса.
     *
     * @param string $link
     * @param array $getParams
     * @param string $method
     * @param mixed | null $data
     * @return JSON | array
     */
    public function callMethod($link, $getParams = [], $method = 'GET', $data = null)
    {
        if ($getParams){
            $link           = $link . '?' . http_build_query($getParams);
        }
        $this->request  = $this->createRequest($method, $link);
        $this->request->setOptions([
            'maxRedirects' => 0,
        ]);

        if ($data) {
            $this->request->setData($data);
        }
        if (!Yii::$app->user->isGuest) {
            //авторизованый пользователь
            /** @var \yii\authclient\Collection $authCollection */
            $authCollection = \Yii::$app->authClientCollection;
            /** @var \app\components\XapiAuthClient $XapiAuthClient */
            $XapiAuthClient = $authCollection->getClient('xapi');
            $token = $XapiAuthClient->getAccessToken();
            if ($token) {
                \yii::$app->getResponse()->redirect(Url::toRoute($this->authRedirect))->send();
                \yii::$app->end();
                $this->request->setHeaders(['Authorization' => 'Bearer ' . $token->params['access_token']]);
            } else {
                \yii::$app->getResponse()->redirect(Url::toRoute($this->authRedirect))->send();
                \yii::$app->end();
           }
        }
        $this->response = $this->request->send();

        $this->handleResult();
        $result = [
            'status'       => $this->response->isOk,
            'data'         => $this->response->data,
            'headers'      => $this->response->headers,
            'returnStatus' => $this->responseStatus,
            'authError'    => false,
        ];
        try {
            switch ($this->responseStatus) {
                case 0:
                case self::RETURN_AUTH_ERROR:
                    $result['authError'] = 'Authorization required';
                break;
                case self::RETURN_PERMS_ERROR:
                    $result['authError'] = 'Success denied';
                    break;
            }
        } catch (\Exception $ex) {
            //exit(\yii::$app->runAction($this->authRedirect));
            $result['authError'] = 'Authorization required';
        }

        if ($this->ajaxResponse) {
            $this->jHeaders();
            //\yii::trace(\yii\helpers\VarDumper::dumpAsString(\yii::$app->response->headers), "upzapi");
            return $this->ajaxResult;
        } else {
            return $result;
        }
    }

    /**
     * Обрабатывает ответ API, определяет статус,
     * производит установленные действия над результатами.
     *
     * @return integer
     */
    protected function handleResult()
    {
        $isOk = $this->response->isOk;
        $httpCode = (int) $this->response->headers->get("http-code");
     //   $this->ajaxResult = $this->response->data;
        /** Nonsens answer, e.g. Yii2 log  */
        if ($this->isNonsens()) {
            $this->log();
            $this->isNoSucces();
            if ($this->nonsensCallback && ($this->nonsensCallback instanceof \Closure)) {
                $this->ajaxResult = call_user_func_array($this->nonsensCallback,
                    [$this->response->headers]);
            }
            if ($this->nonsensMessage) {
                $this->setMessage($this->nonsensMessageType,
                    $this->nonsensMessage);
            } else {
                if ($this->useDefNonsensMessage) {
                    $this->setMessage($this->nonsensMessageType,
                        "{$this->defName}. {$this->defMessage}");
                }
            }
            if ($this->nonsensRedirect) {
                \yii::$app->getResponse()->redirect($this->nonsensRedirect)->send();
                \yii::$app->end();
            }
            return $this->responseStatus;
        }
        /** No authorization */
        if ($this->isAuthErr()) {
            $this->log();
            $this->isNoSucces();
            if ($this->authCallback) {
                $this->ajaxResult = call_user_func_array($this->authCallback,
                    [$this->response->data, $this->response->headers]);
            }
            if ($this->authMessage) {
                $this->setMessage($this->authMessageType, $this->authMessage);
            } else {
                if ($this->useDefAuthMessage) {
                    $this->setMessage($this->authMessageType,
                        "{$this->defName}. {$this->defMessage}");
                }
            }
            if ($this->authRedirect) {
                \yii::$app->getResponse()->redirect($this->authRedirect)->send();
                \yii::$app->end();
            }
            return $this->responseStatus;
        }
        /** No authorization permissions */
        if ($this->isAuthPermsErr()) {
            $this->log();
            $this->isNoSucces();
            if ($this->permsCallback && ($this->permsCallback instanceof \Closure)) {
                $this->ajaxResult = call_user_func_array($this->permsCallback,
                    [$this->response->data, $this->response->headers]);
            }
            if ($this->permsMessage) {
                $this->setMessage($this->permsMessageType, $this->permsMessage);
            } else {
                if ($this->useDefPermsMessage) {
                    $this->setMessage($this->permsMessageType,
                        "{$this->defName}. {$this->defMessage}");
                }
            }
            if ($this->permsRedirect) {
                \yii::$app->getResponse()->redirect($this->permsRedirect)->send();
                \yii::$app->end();
            }
            return $this->responseStatus;
        }
        /** API exceptions */
        if ($this->isException()) {
            $this->log();
            $this->isNoSucces();
            if ($this->exceptionMessage) {
                //\yii::trace(\yii\helpers\VarDumper::dumpAsString([$this->exceptionMessageType, $this->exceptionMessage]),
                //        "upzapi");
                $this->setMessage($this->exceptionMessageType,
                    $this->exceptionMessage);
            } else {
                if ($this->useDefExceptMessage) {
                    $this->setMessage($this->exceptionMessageType,
                        "{$this->defName}. {$this->defMessage}");
                }
            }
            if ($this->exceptionCallback && ($this->exceptionCallback instanceof \Closure)) {
                $this->ajaxResult = call_user_func_array($this->exceptionCallback,
                    [$this->response->data, $this->response->headers]);
            }
            if ($this->exceptionRedirect) {
                \yii::$app->getResponse()->redirect($this->exceptionRedirect)->send();
                \yii::$app->end();
            }
            /* @todo Сделать коды ошибочных ответов апи в клиента */
            //\yii::$app->response->setStatusCode(400);
            return $this->responseStatus;
        }
        /** Answer as validation errors */
        if ($this->isValidErr()) {
            $this->log();
            try {
                $errors = [];
                if (count($this->response->data) && (!is_null($this->model) && ($this->model instanceof \yii\base\Model))) {
                    foreach ($this->response->data as $error) {
                        $this->model->addError($error["field"],
                            $error["message"]);
                        $errors[] = "{$error["field"]}: {$error["message"]}";
                    }
                }
                if ($this->invalidMessage) {
                    $this->setMessage($this->invalidMessageType,
                        $this->invalidMessage);
                } else {
                    $this->setMessage($this->invalidMessageType,
                        implode("\n", $errors));
                }
                if ($this->invalidCallback && ($this->invalidCallback instanceof \Closure)) {
                    $this->ajaxResult = call_user_func_array($this->invalidCallback,
                        [$this->response->data, $this->response->headers]);
                }
                if ($this->invalidRedirect) {
                    \yii::$app->getResponse()->redirect($this->invalidRedirect)->send();
                    \yii::$app->end();
                }
            } catch (Exception $ex) {

            }
            return $this->responseStatus;
        }
        /** Success answer */
        if ($this->isSuccess()) {
            try {
                if ($this->model instanceof \yii\base\Model) {
                    $this->model->setAttributes($this->response->data, true);
                }
            } catch (Exception $ex) {
                \yii::error(VarDumper::dumpAsString($ex), "upzapi");
            }
            if ($this->successCallback && ($this->successCallback instanceof \Closure)) {
                $this->ajaxResult = call_user_func_array($this->successCallback,
                    [$this->response->data, $this->response->headers, $this]);
            }
            if ($this->successMessage) {
                $this->setMessage($this->successMessageType,
                    $this->successMessage);
            }
            if ($this->successRedirect) {
                \yii::$app->getResponse()->redirect($this->successRedirect)->send();
                \yii::$app->end();
            }
            return $this->responseStatus;
        }
    }

    /**
     * @param string $method
     * @param string $url
     * @return Request
     */
    protected function createRequest($method = 'GET', $url) {
        return $this->getHttpClient()
            ->createRequest()
            ->setFormat(Client::FORMAT_JSON)
            ->setMethod($method)
            ->setUrl($this->apiBaseUrl . $url);
    }

    /**
     * Returns HTTP client.
     * @return Client internal HTTP client.
     * @since 2.1
     */
    public function getHttpClient() {
        if (!is_object($this->_httpClient)) {
            $this->_httpClient = $this->createHttpClient($this->_httpClient);
        }
        return $this->_httpClient;
    }

    /**
     * Sets HTTP client to be used.
     * @param array|Client $httpClient internal HTTP client.
     * @since 2.1
     */
    public function setHttpClient($httpClient) {
        $this->_httpClient = $httpClient;
    }

    /**
     * Creates HTTP client instance from reference or configuration.
     * @param string|array $reference component name or array configuration.
     * @return Client HTTP client instance.
     * @since 2.1
     */
    protected function createHttpClient($reference) {
        return Instance::ensure($reference, Client::className());
    }

    /**
     * Запускает проверку версии разрешений пользователя в компоненте AuthManager.
     * @return void
     */
    protected function checkRBACVersion() {
        try {
            \yii::$app->authManager->checkVersion($this->response->getHeaders()->get("X-RBACVersion"));
        } catch (\Exception $ex) {
            \yii::error($ex, "upzapi");
        }
    }

    protected function jHeaders() {
        \yii::$app->response->getHeaders()
            ->set("Cache-control",
                "no-store,private,no-cache,must-revalidate")
            ->add("Cache-control",
                "pre-check=0,post-check=0,max-age=0,max-stale=0")
            ->set("Pragma", "no-cache")
            ->add("Pragma", "public")
            ->set("Expires", gmdate("D, d M Y H:i:s", 0) . "GMT")
            ->add("Expires", 0)
            ->add("Last-Modified", gmdate("D, d M Y H:i:s") . "GMT");
    }

    public function setMessage($messageType, $message) {
        \yii::$app->getResponse()->getHeaders()->add(self::AJAX_MESSAGE_HEADER_NAME,
            json_encode(["t" => $messageType, "m" => $message]));
        if (!$this->ajaxResponse) {
            \yii::$app->getSession()->setFlash($messageType, $message);
        }
    }

    protected function isNoSucces() {
        if ($this->responseStatus != 1) {
            if ($this->noSuccessMessage) {
                $this->setMessage($this->noSuccessMessageType,
                    $this->noSuccessMessage);
            }
            if ($this->noSuccessCallback && ($this->noSuccessCallback instanceof \Closure)) {
                $this->ajaxResult = call_user_func_array($this->noSuccessCallback,
                    [$this->response->data, $this->response->headers, $this]);
            }
            if (!is_null($this->noSuccessRedirect)) {
                \yii::$app->getResponse()->redirect($this->noSuccessRedirect)->send();
                \yii::$app->end();
            }
        }
    }

    /**
     * Проверяет, является ли ответ ошибкой авторизации API.
     * Если это так, устанавливает статус ответа в RETURN_AUTH_ERROR (16),
     * соответствующие сообщения по-умолчанию.
     * @return boolean
     */
    protected function isAuthErr() {
        $ret = false;
        /*
        $ret = strpos($this->response->headers->get("content-type"), "json") &&
            (int) $this->response->headers->get("http-code") == 401 &&
            $this->response->data["type"] == "yii\\web\\UnauthorizedHttpException";
        */
        if ((int) $this->response->headers->get("http-code") == 400) {
            $this->responseStatus = self::RETURN_AUTH_ERROR;
            $this->defName        = "Права доступа";
            $this->defMessage     = "Требуется авторизация";
        }
        return $ret;
    }

    protected function isTokenExpireErr() {
        $ret = false;
        if ((int) $this->response->headers->get("http-code") == 400) {
            $this->responseStatus = self::RETURN_AUTH_ERROR;
            $this->defName        = "Права доступа";
            $this->defMessage     = "Требуется авторизация";
        }
        return $ret;
    }

    protected function isAuthPermsErr()
    {
        $ret = false;
        $ret = strpos($this->response->headers->get("content-type"), "json") &&
            (int) $this->response->headers->get("http-code") == 403;
        if ($ret) {
            $this->responseStatus = self::RETURN_PERMS_ERROR;
            $this->defName        = \yii::t("app", "Права доступа");
            $this->defMessage     = \yii::t("app",
                "Вы не можете выполнять это действие");
        }
        return $ret;
    }

    protected function isNonsens() {
        $ret = false;
        $ret = strpos($this->response->headers->get("content-type"), "html") &&
            (int) $this->response->headers->get("http-code") >= 400;
        if ($ret) {
            $this->response->data = [];
            $this->defName        = \yii::t("app", "Ошибка сервера");
            $this->defMessage     = \yii::t("app",
                "Обратитесь к администратору");
            $this->responseStatus = self::RETURN_NONSENS;
        }
        return $ret;
    }

    protected function isException() {
        $ret = false;
        $ret = strpos($this->response->headers->get("content-type"), "json") &&
            (int) $this->response->headers->get("http-code") > 400 &&
            is_array($this->response->data) &&
            array_key_exists("name", $this->response->data);
        if ($ret) {
            $this->responseStatus = self::RETURN_EXCEPT;
            $this->defName        = $this->response->data["name"];
            $this->defMessage     = $this->response->data["message"];
        }
        return $ret;
    }

    protected function isValidErr() {
        $ret = false;
        $ret = strpos($this->response->headers->get("content-type"), "json") &&
            (int) $this->response->headers->get("http-code") == 422 &&
            !$this->response->isOk;
        if ($ret) {
            $this->responseStatus = self::RETURN_VALIDATOR_ERRORS;
        }
        return $ret;
    }

    protected function isSuccess() {
        $ret = false;
        $ret = strpos($this->response->headers->get("content-type"), "json") &&
            (int) $this->response->headers->get("http-code") < 400 &&
            $this->response->isOk;
        if ($ret) {
            $this->responseStatus = self::RETURN_SUCCESS;
        }
        return $ret;
    }

    /** Handler setters */

    /**
     * @param \yii\base\Model $model
     * @return $this
     */
    public function setModel(&$model) {
        $this->model = $model;
        return $this;
    }

    /**
     * @param string $message
     * @param string $messageType
     * @return $this
     */
    public function setSuccessMessage($message, $messageType = "success") {
        $this->successMessage     = $message;
        $this->successMessageType = $messageType;
        return $this;
    }

    /**
     * @param array|string $redirect
     * @return $this
     */
    public function setSuccessRedirect($redirect) {
        $this->successRedirect = $redirect;
        return $this;
    }

    /**
     * @param \Closure $calable
     * @return $this
     */
    public function setSuccessCallback($calable) {
        if ($calable instanceof \Closure) {
            $this->successCallback = $calable;
        }
        return $this;
    }

    /**
     * @param string $message
     * @param string $messageType
     * @return $this
     */
    public function setInvalidMessage($message, $messageType = "warning") {
        $this->invalidMessage     = $message;
        $this->invalidMessageType = $messageType;
        return $this;
    }

    /**
     * @param array|string $redirect
     * @return $this
     */
    public function setInvalidRedirect($redirect) {
        $this->invalidRedirect = $redirect;
        return $this;
    }

    /**
     * @param \Closure $calable
     * @return $this
     */
    public function setInvalidCallback($calable) {
        if ($calable instanceof \Closure) {
            $this->invalidCallback = $calable;
        }
        return $this;
    }

    /**
     * @param string $message
     * @param string $messageType
     * @return $this
     */
    public function setExceptionMessage($message, $messageType = "error") {
        $this->exceptionMessage     = $message;
        $this->exceptionMessageType = $messageType;
        return $this;
    }

    /**
     * @param array|string $redirect
     * @return $this
     */
    public function setExceptionRedirect($redirect) {
        $this->exceptionRedirect = $redirect;
        return $this;
    }

    /**
     * @param \Closure $calable
     * @return $this
     */
    public function setExceptionCallback($calable) {
        if ($calable instanceof \Closure) {
            $this->exceptionCallback = $calable;
        }
        return $this;
    }

    /**
     * @param string $message
     * @param string $messageType
     * @return $this
     */
    public function setNonsensMessage($message, $messageType = "error") {
        $this->nonsensMessage     = $message;
        $this->nonsensMessageType = $messageType;
        return $this;
    }

    /**
     * @param array|string $redirect
     * @return $this
     */
    public function setNonsensRedirect($redirect) {
        $this->nonsensRedirect = $redirect;
        return $this;
    }

    /**
     * @param \Closure $calable
     * @return $this
     */
    public function setNonsensCallback($calable) {
        if ($calable instanceof \Closure) {
            $this->nonsensCallback = $calable;
        }
        return $this;
    }

    /**
     * @param string $message
     * @param string $messageType
     * @return $this
     */
    public function setPermsMessage($message, $messageType = "error") {
        $this->permsMessage     = $message;
        $this->permsMessageType = $messageType;
        return $this;
    }

    /**
     * @param array|string $redirect
     * @return $this
     */
    public function setPermsRedirect($redirect) {
        $this->permsRedirect = $redirect;
        return $this;
    }

    /**
     * @param \Closure $calable
     * @return $this
     */
    public function setPermsCallback($calable) {
        if ($calable instanceof \Closure) {
            $this->permsCallback = $calable;
        }
        return $this;
    }

    /**
     * @param string $message
     * @param string $messageType
     * @return $this
     */
    public function setAuthMessage($message, $messageType = "error") {
        $this->authMessage     = $message;
        $this->authMessageType = $messageType;
        return $this;
    }

    /**
     * @param array|string $redirect
     * @return $this
     */
    public function setAuthRedirect($redirect) {
        $this->authRedirect = $redirect;
        return $this;
    }

    /**
     * @param \Closure $calable
     * @return $this
     */
    public function setAuthCallback($calable) {
        if ($calable instanceof \Closure) {
            $this->authCallback = $calable;
        }
        return $this;
    }

    /**
     * @param array|string $redirect
     * @return $this
     */
    public function setNoSuccessRedirect($redirect) {
        $this->noSuccessRedirect = $redirect;
        return $this;
    }

    /**
     * @param string $message
     * @param string $messageType
     * @return $this
     */
    public function setNoSuccessMessage($message, $messageType = "error") {
        $this->noSuccessMessage     = $message;
        $this->noSuccessMessageType = $messageType;
        return $this;
    }

    /**
     * @param \Closure $calable
     * @return $this
     */
    public function setNoSuccessCallback($calable) {
        if ($calable instanceof \Closure) {
            $this->noSuccessCallback = $calable;
        }
        return $this;
    }

    /**
     * @param bool|integer $val
     * @return $this
     */
    public function useDefaultNonsensMessage($val) {
        $this->useDefNonsensMessage = (bool) $val;
        return $this;
    }

    /**
     * @param bool|integer $val
     * @return $this
     */
    public function useDefaultExceptionMessage($val) {
        $this->useDefExceptMessage = (bool) $val;
        return $this;
    }

    /**
     * @param bool|integer $val
     * @return $this
     */
    public function useDefaultPermissionsMessage($val) {
        $this->useDefPermsMessage = (bool) $val;
        return $this;
    }

    /**
     * @param bool|integer $val
     * @return $this
     */
    public function useDefaultAuthorizationMessage($val) {
        $this->useDefAuthMessage = (bool) $val;
        return $this;
    }

    /**
     * Устанавливает признак ajax-ответа
     * @param boolean $val
     * @return $this
     */
    public function isAjax($val = true) {
        $this->ajaxResponse = (bool) $val;
        if ($this->ajaxResponse === true) {
            \yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        } else {
            \yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        }
        return $this;
    }

    protected function log($level = "error", $message = "") {
        $msg            = [];
        $msg["msg"]     = "{$message}";
        $msg["url"]     = $this->request->fullUrl;
        $msg["content"] = $this->request->content;

        switch ($this->responseStatus) {
            case self::RETURN_NONSENS:
                $msg["msg"]              = "Nonsens";
                $msg["response_content"] = strip_tags($this->response->content);
                break;
            case self::RETURN_EXCEPT:
                $msg["msg"]              = "Exception";
                $msg["response"]         = $this->response->data;
                break;
            case self::RETURN_AUTH_ERROR:
                $session                 = $_SESSION;
                unset($session["assigments"]);
                unset($session["companyPermissions"]);
                unset($session["userCompanies"]);
                unset($session["userOwnCompanies"]);
                $msg["msg"]              = "Authorization error";
                $msg["session"]          = $session;
                break;
            case self::RETURN_PERMS_ERROR:
                $session                 = $_SESSION;
                unset($session["assigments"]);
                unset($session["userCompanies"]);
                unset($session["userOwnCompanies"]);
                unset($session["companyPermissions"]);
                $msg["msg"]              = "Permission error";
                $msg["session"]          = $session;
                break;
            case self::RETURN_VALIDATOR_ERRORS:
                $msg["msg"]              = "Validation errors";
                $msg["errors"]           = $this->response->data;
                $msg["headers"]          = $this->response->headers;
                break;
        }
        $msg["msg"] = "{$this->responseStatus}. {$msg["msg"]}";
     //   \yii::$level(\yii\helpers\VarDumper::dumpAsString($msg), "upzapi");
    }

}
