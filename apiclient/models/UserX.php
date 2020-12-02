<?php

namespace app\models;


use common\helpers\Functions;

class UserX extends \yii\web\User
{
    public function getApiLoginsInfo()
    {
        $tokens = UserToken::find()
            ->where(['client_id' => $this->id])
            ->asArray()
            ->all();
        $ret = '';
        foreach ($tokens as $userToken) {
            $expires_in = (int) $userToken['created_at'] + ((int) $userToken['expires_in']);
            $ret .= Functions::intToDateTime($expires_in) . PHP_EOL;
        }
        return $ret;
    }

    public function getApiUserId()
    {
        $tmp = \Yii::$app->authClientCollection->getClients();
        $providerFullId = \Yii::$app->authClientCollection->getClient('xapi')->fullClientId;
        $token = UserToken::find()
            ->where(['client_id' => $this->id])
            ->andWhere(['provider' => $providerFullId])
            ->asArray()
            ->one();
        $ret = (!empty($token)) ? $token['api_id'] : false;

        return $ret;
    }

}

