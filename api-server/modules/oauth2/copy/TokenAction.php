<?php
/**
 * @link https://github.com/borodulin/yii2-oauth-server
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-oauth-server/blob/master/LICENSE
 */

namespace apiserver\modules\oauth2;

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
        'authorization_code' => 'conquer\oauth2\granttypes\Authorization',
        'refresh_token' => 'conquer\oauth2\granttypes\RefreshToken',
        'client_credentials' => 'conquer\oauth2\granttypes\ClientCredentials',
//         'password' => 'conquer\oauth2\granttypes\UserCredentials',
//         'urn:ietf:params:oauth:grant-type:jwt-bearer' => 'conquer\oauth2\granttypes\JwtBearer',
    ];

    public function init()
    {
        Yii::$app->response->format = $this->format;
        $this->controller->enableCsrfValidation = false;
    }

    public function run()
    {

        if (!$grantType = BaseModel::getRequestValue('grant_type')) {
            throw new Exception(Yii::t('conquer/oauth2', 'The grant type was not specified in the request.'));
        }
        if (isset($this->grantTypes[$grantType])) {
            \yii::trace('************************************************ grantType=' . $grantType, "dbg");
            \yii::trace(\yii\helpers\VarDumper::dumpAsString($this->grantTypes[$grantType]), "dbg");
            $grantModel = Yii::createObject($this->grantTypes[$grantType]);
        } else {
            throw new Exception(Yii::t('conquer/oauth2', 'An unsupported grant type was requested.'), Exception::UNSUPPORTED_GRANT_TYPE);
        }

       // $grantModel->validate();
        if (!$grantModel->validate()){
            \yii::trace('************************************************ grantType NO VALID', "dbg");
            \yii::trace(\yii\helpers\VarDumper::dumpAsString($grantModel->getErrors()), "dbg");
        } else {
            \yii::trace('************************************************ grantType VALID', "dbg");
        }

        Yii::$app->response->data = $grantModel->getResponseData();
    }
}
