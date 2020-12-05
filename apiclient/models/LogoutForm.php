<?php

namespace app\models;

use app\components\models\Provider;
use Yii;
use yii\base\Model;
use yii\di\Instance;
use yii\httpclient\Client;
use yii\httpclient\Request;
use app\models\User;
//use app\components\ModelSessionStorageBehavior;


/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LogoutForm extends Model
{

    public $provider;
    protected $_user = false;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['provider',  'string', 'min' => 3, 'max' => 50],
        ];
    }

    public function getIdentity() {
        return $this->_user;
    }

    public function attributeLabels() {
        return [
            'provider' => 'Апи провайдер',
        ];
    }

    public static function providers()
    {
        return array_merge(['all' => 'Все'], Provider::getClientsList());
    }


    public function providersLogout()
    {
        if ($this->validate()) {
            try{
                $clientId = \Yii::$app->user->id;
                switch ($this->provider){
                    case 'none':
                        break;
                    case 'all':
                        $t=Yii::$app->authClientCollection->clients;
                        foreach ($t as $client){
                            $clientApi = Yii::$app->authClientCollection->getClient($client->clientId);
                            $provider = $clientApi->fullClientId;
                            $userApi = UserToken::find()
                                ->select('api_id')
                                ->where(['client_id' => $clientId, 'provider' => $provider])
                                ->one();
                            if (!empty($userApi)){
                                if (!$clientApi->removeTokens($userApi->api_id)){
                                    $this->addError('provider', $client->errorMessage);
                                    return false;
                                }
                            } else {
                                $this->addError('provider', "Token for $provider not found in DB");
                            }
                        }
                        break;
                    default:
                        $clientApi = Yii::$app->authClientCollection->getClient($this->provider);
                        $provider = $clientApi->fullClientId;
                        $userApi = UserToken::find()
                            ->select('api_id')
                            ->where(['client_id' => $clientId, 'provider' => $provider])
                            ->one();
                        if (!empty($userApi)){
                            if (!$clientApi->removeTokens( $userApi->api_id)){
                                $this->addError('provider', $clientApi->errorMessage);
                                return false;
                            }
                        } else {
                            $this->addError('provider', "Token for $this->provider not found in DB");
                            return false;
                        }
                }

                return !$this->hasErrors();
            } catch (\Exception $e){
                $this->addError('provider', $e->getMessage());
                return false;
            }
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

}
