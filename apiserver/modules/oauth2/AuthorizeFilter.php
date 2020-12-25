<?php
namespace apiserver\modules\oauth2;

use apiserver\modules\oauth2\responsetypes\Authorization;
use common\helpers\Functions;
use Yii;
use yii\base\ActionFilter;

class AuthorizeFilter extends ActionFilter
{
    private $_responseType;

    public $responseTypes = [
        'token' => 'apiserver\modules\oauth2\responsetypes\Implicit',
        'code' => 'apiserver\modules\oauth2\responsetypes\Authorization',
    ];

    /**
     *
     * @var boolean
     */
    public $allowImplicit = true;

    public $storeKey = 'ear6kme7or19rnfldtmwsxgzxsrmngqw';

    public function init()
    {
        Functions::log("SERVER API --- class AuthorizeFilter extends ActionFilter public function init()");
        Functions::logRequest();

        if (!$this->allowImplicit) {
            unset($this->responseTypes['token']);
        }
    }

    /**
     * Performs OAuth 2.0 request validation and store granttype object in the session,
     * so, user can go from our authorization server to the third party OAuth provider.
     * You should call finishAuthorization() in the current controller to finish client authorization
     * or to stop with Access Denied error message if the user is not logged on.
     * @param \yii\base\Action $action
     * @return bool
     * @throws Exception
     */
    public function beforeAction($action)
    {
        Functions::log("SERVER API --- public function beforeAction(action)");

        if (!$responseType = BaseModel::getRequestValue('response_type')) {
            Functions::log("SERVER API --- Invalid or missing response type.");
            throw new Exception(Yii::t('conquer/oauth2', 'Invalid or missing response type.'));
        }

        Functions::log("SERVER API --- Пытаемся обработать responseType=$responseType ...");
        if (isset($this->responseTypes[$responseType])) {
            $this->_responseType = Yii::createObject($this->responseTypes[$responseType]);
        } else {
            Functions::log("SERVER API --- An unsupported response type was requested.");
            throw new Exception(Yii::t('conquer/oauth2', 'An unsupported response type was requested.'), Exception::UNSUPPORTED_RESPONSE_TYPE);
        }

        Functions::log("SERVER API --- Создали объект $responseType");
        Functions::log("SERVER API --- валидация $responseType");

        if ($this->_responseType->validate()) {
            Functions::log("SERVER API --- прошла");
        } else {
            Functions::log("SERVER API --- не прошла");
            Functions::log($this->_responseType->getErrors());
        }

        if ($this->storeKey) {
            Yii::$app->session->set($this->storeKey, serialize($this->_responseType));
            Functions::log("SERVER API --- записываем сериализованый responseType в сессию");
        }

        return true;
    }

    /**
     * If user is logged on, do oauth login immediatly,
     * continue authorization in the another case
     * @param \yii\base\Action $action
     * @param mixed $result
     * @return mixed|null
     */
    public function afterAction($action, $result)
    {
        if (Yii::$app->user->isGuest) {
            return $result;
        } else {
            $this->finishAuthorization();
        }
        return null;
    }

    /**
     * @throws Exception
     * @return \conquer\oauth2\BaseModel
     */
    protected function getResponseType()
    {
        if (empty($this->_responseType) && $this->storeKey) {
            if (Yii::$app->session->has($this->storeKey)) {
                $this->_responseType = unserialize(Yii::$app->session->get($this->storeKey));
            } else {
                throw new Exception(Yii::t('conquer/oauth2', 'Invalid server state or the User Session has expired.'), Exception::SERVER_ERROR);
            }
        }
        return $this->_responseType;
    }

    /**
     * Finish oauth authorization.
     * Builds redirect uri and performs redirect.
     * If user is not logged on, redirect contains the Access Denied Error
     */
    public function finishAuthorization()
    {
        Functions::log("SERVER API --- class AuthorizeFilter extends ActionFilter public function finishAuthorization()");

        /** @var Authorization $responseType */
        $responseType = $this->getResponseType();
        Functions::log("SERVER API --- responseType = :");

        if (Yii::$app->user->isGuest) {
            Functions::log("SERVER API --- The User denied access to your application");

            $responseType->errorRedirect(Yii::t('conquer/oauth2', 'The User denied access to your application.'), Exception::ACCESS_DENIED);
        }
        $parts = $responseType->getResponseData();
        Functions::log("SERVER API --- parts = :");
        Functions::log($parts);

        $redirectUri = http_build_url($responseType->redirect_uri, $parts, HTTP_URL_JOIN_QUERY | HTTP_URL_STRIP_FRAGMENT);

        if (isset($parts['fragment'])) {
            $redirectUri .= '#' . $parts['fragment'];
        }
        Functions::log("SERVER API --- redirectUri = $redirectUri");
      //  Functions::log("SERVER API --- response:");
      //  Functions::log(Yii::$app->response);

        Functions::log("SERVER API --- редиректимся на redirectUri ...");

        Yii::$app->response->redirect($redirectUri);
    }

    /**
     * @return boolean
     */
    public function getIsOauthRequest()
    {
        return !empty($this->storeKey) && Yii::$app->session->has($this->storeKey);
    }
}
