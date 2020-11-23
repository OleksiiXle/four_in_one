<?php
/**
 * @link https://github.com/borodulin/yii2-oauth2-server
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-oauth2-server/blob/master/LICENSE
 */

namespace apiserver\modules\oauth2\console;

use yii\console\Controller;
use apiserver\modules\oauth2\models\AuthorizationCode;
use apiserver\modules\oauth2\models\RefreshToken;
use apiserver\modules\oauth2\models\AccessToken;

/**
 * @author Andrey Borodulin
 */
class Oauth2Controller extends Controller
{
    public function actionIndex()
    {
    }

    public function actionClear()
    {
        AuthorizationCode::deleteAll(['<', 'expires', time()]);
        RefreshToken::deleteAll(['<', 'expires', time()]);
        AccessToken::deleteAll(['<', 'expires', time()]);
    }
}
