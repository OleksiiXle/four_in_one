<?php
/**
 * @link https://github.com/borodulin/yii2-oauth-server
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-oauth-server/blob/master/LICENSE
 */

namespace apiserver\modules\oauth2;

use common\helpers\Functions;
use Yii;
use yii\base\Action;
use yii\web\Response;

/**
 * @author Andrey Borodulin
 */
class TokenAction extends Action
{
    /** Format of response
     * @var string
     */
    public $format = Response::FORMAT_JSON;

    public $grantTypes = [
        'authorization_code' => 'apiserver\modules\oauth2\granttypes\Authorization',
        'refresh_token' => 'apiserver\modules\oauth2\granttypes\RefreshToken',
        'client_credentials' => 'apiserver\modules\oauth2\granttypes\ClientCredentials',
        'logout' => 'apiserver\modules\oauth2\granttypes\Logout',

//         'password' => 'conquer\oauth2\granttypes\UserCredentials',
//         'urn:ietf:params:oauth:grant-type:jwt-bearer' => 'conquer\oauth2\granttypes\JwtBearer',
    ];

    public function init()
    {
        Functions::log('SERVER API ---  TokenAction init()');
        Functions::logRequest();

        Yii::$app->response->format = $this->format;
        $this->controller->enableCsrfValidation = false;
    }

    public function run()
    {
        $grantType = BaseModel::getRequestValue('grant_type');

        if (!$grantType) {
            throw new Exception(Yii::t('conquer/oauth2', 'The grant type was not specified in the request.'));
        }
        if (isset($this->grantTypes[$grantType])) {
            Functions::log('SERVER API --- создаем объект grantModel  - grant_type = ' . (!empty($grantType) ? $grantType : 'none'));

            $grantModel = Yii::createObject($this->grantTypes[$grantType]);
        } else {
            throw new Exception(Yii::t('conquer/oauth2', 'An unsupported grant type was requested.'), Exception::UNSUPPORTED_GRANT_TYPE);
        }
        Functions::log('аттрибуты:');
        Functions::log($grantModel->getAttributes());
        Functions::log('SERVER API --- проводим его валидацию');

        $grantModel->validate();

        Functions::log('SERVER API --- добавляем в ответ:');
        $responseData = $grantModel->getResponseData();
        Functions::log($responseData);
        Yii::$app->response->data = $responseData;
    }

    protected function beforeRun()
    {
        return true;
    }

}
