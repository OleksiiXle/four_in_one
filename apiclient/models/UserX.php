<?php

namespace app\models;


use common\helpers\Functions;

class UserX extends \yii\web\User
{
    private $_apiLoginInfo = null;
    private $_apiesNames = null;

    /**
     * @return null
     */
    public function getApiLoginInfo()
    {
        if ($this->_apiLoginInfo === null) {
            $this->_apiLoginInfo = [];
            $tokens = UserToken::find()
                ->where(['client_id' => $this->id])
                ->asArray()
                ->all();
            foreach ($tokens as $userToken) {
                $expires_in = (int) $userToken['created_at'] + ((int) $userToken['expires_in']);
                $this->_apiLoginInfo[$userToken['provider_id']] = [
                  'providerFullName' => $userToken['provider'],
                  'apiUserId' => $userToken['api_id'],
                  'accessToken' => $userToken['access_token'],
                  'expires' => Functions::intToDateTime($expires_in),
                  'tokenType' => $userToken['token_type'],
                  'refreshToken' => $userToken['refresh_token'],
                  'apiPepmissions' => (!empty($userToken['permissions'])) ? json_decode($userToken['permissions'], true) : [],
                ];
            }
        }
        return $this->_apiLoginInfo;
    }
    /**
     * @return null
     */
    public function getApiesNames()
    {
        if ($this->_apiesNames === null) {
            $this->_apiesNames = '';
            if (!empty($this->apiLoginInfo)) {
                foreach ($this->apiLoginInfo as $name => $data) {
                    $this->_apiesNames .= $name . ' ';
                }
            }
        }
        return $this->_apiesNames;
    }

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

