<?php
namespace apiserver\modules\oauth2\models;

use apiserver\models\UserM;
use common\models\User;
use Yii;
use common\helpers\Functions;


/**
 * Login form
 */
class SignupForm extends UserM
{
    const DEFAULT_ROLE = 'user';

    public $rememberMe = true;

    private $_user;

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }


    public function getErrorsWithAttributesLabels()
    {
        $errorsArray = $this->getErrors();
        $ret = [];
        foreach ($errorsArray as $attributeName => $attributeErrors ){
            foreach ($attributeErrors as $attributeError)
                $ret[$this->getAttributeLabel($attributeName)] = $attributeError;
        }
        return $ret;
    }

    public function showErrors()
    {
        $ret = $lines = '';
        $header = '<p>' . Yii::t('yii', 'Please fix the following errors:') . '</p>';
        $errorsArray = $this->getErrorsWithAttributesLabels();
        foreach ($errorsArray as $attrName => $errorMessage){
            $lines .= "<li>$attrName : $errorMessage</li>";
        }
        if (!empty($lines)) {
            $ret = "<div>$header<ul>$lines</ul></div>" ;
        }

        return $ret;

    }


    /**
     * Signs user up.
     *
     * @return bool whether the creating new account was successful and email was sent
     */
    public function signup()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            Functions::log('************************************************ signup start');
            $this->setPassword($this->password);
            $this->generateAuthKey();
            Functions::log('************************************ try to save UserM');
            Functions::log($this->getAttributes());
            if ($this->save()) {
                $userData = new UserData();
                $userData->user_id = $this->id;
                $userData->first_name = $this->first_name;
                $userData->middle_name = $this->middle_name;
                $userData->last_name = $this->last_name;
                Functions::log('************************************ try to save UserData');
                if (!$userData->save()){
                    foreach ($userData->getErrors() as $key => $err){
                        $this->addError('username', $err[0] );
                    }
                    $transaction->rollBack();
                    Functions::log('************************************************ signup FAILED UserData');
                    Functions::log($this->getErrorsWithAttributesLabels());

                    return false;
                }
                $auth = Yii::$app->authManager;
                $userRole = $auth->getRole(self::DEFAULT_ROLE);
                if (isset($userRole)){
                    $ret = $auth->assign($userRole, $this->id);
                    if (!$ret){
                        $this->addError('username', "Помилка призначення ролі ");
                        $transaction->rollBack();
                        Functions::log('************************************************ signup FAILED Помилка призначення ролі ');
                        Functions::log($this->getErrorsWithAttributesLabels());

                        return false;
                    }
                } else {
                    $this->addError('username', "Роль не знайдена");
                    $transaction->rollBack();
                    Functions::log('************************************************ signup FAILED Роль не знайдена');
                    Functions::log($this->getErrorsWithAttributesLabels());

                    return false;
                }

            } else {
                $transaction->rollBack();
                Functions::log('************************************************ signup FAILED UserM');
                Functions::log($this->getErrorsWithAttributesLabels());

                return false;
            }
            $transaction->commit();

            $ret = Yii::$app->getUser()->login($this->getUser(), 3600 * 24 * 30/*$this->rememberMe ? 3600 * 24 * 30 : 0*/);
            return $ret;
        } catch (\Exception $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            /*
            $errMsg =  str_replace(PHP_EOL, '<br>', $e->getMessage()
                . '<br>'
                . str_replace(PHP_EOL, '<br>', $e->getTraceAsString()));
            */
           // $errorMessage = $e->getMessage() . PHP_EOL . $e->getTraceAsString();
            $errorMessage = (string) $e;


            $this->addError('username', $errorMessage);
            Functions::log('************************************************ signup FAILED exeption');
            Functions::log($errorMessage);

            return false;
        }
    }
}
