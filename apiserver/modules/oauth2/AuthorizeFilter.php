<?php
/**
 * @link https://github.com/borodulin/yii2-oauth2-server
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-oauth2-server/blob/master/LICENSE
 */

namespace apiserver\modules\oauth2;

use apiserver\modules\oauth2\responsetypes\Authorization;
use common\helpers\Functions;
use Yii;
use yii\base\ActionFilter;

/**
 *
 * @author Andrey Borodulin
 *
 */
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
        if (!$responseType = BaseModel::getRequestValue('response_type')) {
            throw new Exception(Yii::t('conquer/oauth2', 'Invalid or missing response type.'));
        }
        if (isset($this->responseTypes[$responseType])) {
            $this->_responseType = Yii::createObject($this->responseTypes[$responseType]);
        } else {
            throw new Exception(Yii::t('conquer/oauth2', 'An unsupported response type was requested.'), Exception::UNSUPPORTED_RESPONSE_TYPE);
        }

        $this->_responseType->validate();

        if ($this->storeKey) {
            Yii::$app->session->set($this->storeKey, serialize($this->_responseType));
            Functions::log("**** перед $action->id:");
            Functions::log("$responseType=$responseType");
            Functions::log("в сессию storeKey = $this->storeKey:");
            Functions::log(serialize($this->_responseType));
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
        Functions::log("*******finishAuthorization");

        /** @var Authorization $responseType */
        $responseType = $this->getResponseType();

        if (Yii::$app->user->isGuest) {
            $responseType->errorRedirect(Yii::t('conquer/oauth2', 'The User denied access to your application.'), Exception::ACCESS_DENIED);
        }
        $parts = $responseType->getResponseData();

        $redirectUri = http_build_url($responseType->redirect_uri, $parts, HTTP_URL_JOIN_QUERY | HTTP_URL_STRIP_FRAGMENT);

        if (isset($parts['fragment'])) {
            $redirectUri .= '#' . $parts['fragment'];
        }
        Functions::log("redirectUri = $redirectUri");

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
