<?php

namespace common\models\form;

use Yii;
use common\models\User;
use common\models\UserM;
use common\models\UserData;

class Signup extends UserM
{
    const SCENARIO_SIGNUP_BY_HIMSELF  = 'by_himself';
    const SCENARIO_SIGNUP_BY_HIMSELF_WITH_CONFIRMATION  = 'by_himself_with_comfirmation';
    const SCENARIO_SIGNUP_CONFIRMATION  = 'comfirmation';

    public $reCaptcha;

    public function scenarios()
    {
        $ret = parent::scenarios();
        $ret[self::SCENARIO_SIGNUP_BY_HIMSELF] = [
            'username' , 'email',
            'first_name', 'middle_name', 'last_name', 'password', 'retypePassword'        ];
        $ret[self::SCENARIO_SIGNUP_BY_HIMSELF_WITH_CONFIRMATION] = [
            'username' , 'email',
            'first_name', 'middle_name', 'last_name', 'password', 'retypePassword', 'email_confirm_token'
        ];
        $ret[self::SCENARIO_SIGNUP_CONFIRMATION] = [
            '',
        ];
        return $ret ;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        switch ($this->scenario) {
            case self::SCENARIO_SIGNUP_BY_HIMSELF:
                $scenarioRules = [
                    [['username' , 'email',
                        'first_name', 'middle_name', 'last_name', 'password', 'retypePassword'], 'required'],
                    [['retypePassword'], 'compare', 'compareAttribute' => 'password'],
                ];
                break;
            case self::SCENARIO_SIGNUP_BY_HIMSELF_WITH_CONFIRMATION:
                $scenarioRules = [
                    [['username' , 'email', 'email_confirm_token',
                        'first_name', 'middle_name', 'last_name', 'password', 'retypePassword'], 'required'],
                    [['retypePassword'], 'compare', 'compareAttribute' => 'password'],
                    [['email_confirm_token'], 'string'],
                ];
                break;
            case self::SCENARIO_SIGNUP_CONFIRMATION:
                $scenarioRules = [

                ];
                break;
        }

        return array_merge(parent::rules(), $scenarioRules);
    }

    /**
     * @return boolean
     */
    public function signup($byEmail = false)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $user = new static();
            $user->scenario = (!$byEmail)
            ? self::SCENARIO_SIGNUP_BY_HIMSELF
            : self::SCENARIO_SIGNUP_BY_HIMSELF_WITH_CONFIRMATION;

            $user->username = $this->username;
            $user->email = $this->email;
            $user->password = $this->password;
            $user->retypePassword = $this->retypePassword;
            $user->first_name = $this->first_name;
            $user->middle_name = $this->middle_name;
            $user->last_name = $this->last_name;
            //  $user->reCaptcha = $this->reCaptcha;
            if ($byEmail){
                $user->email_confirm_token = \Yii::$app->security->generateRandomString();
                $user->status = UserM::STATUS_WAITING_FOR_EMAIL_CONFIRM;
            } else {
                $user->status = UserM::STATUS_ACTIVE;
            }

            $user->setPassword($this->password);
            $user->generateAuthKey();
            if (!$user->save()) {
                $transaction->rollBack();
                return false;
            }

            $userData = new UserData();
            $userData->user_id = $user->id;
            $userData->first_name = $this->first_name;
            $userData->middle_name = $this->middle_name;
            $userData->last_name = $this->last_name;
            $userData->emails = $this->email;
            if (!$userData->save()) {
                foreach ($userData->getErrors() as $key => $err){
                    $this->addError('username', $err[0] );
                }
                $transaction->rollBack();
                return false;
            }

            $auth = Yii::$app->authManager;
            $userDefaultRole = Yii::$app->configs->userDefaultRole;
            $userRole = $auth->getRole($userDefaultRole);
            if (isset($userRole)){
                $ret = $auth->assign($userRole, $user->id);
                if (!$ret){
                    $this->addError('id', "Role assign error");
                    $transaction->rollBack();
                    return false;
                }
            } else {
                $this->addError('id', "Default role not found");
                $transaction->rollBack();
                return false;
            }

            if ($byEmail) {
                $userSent = User::findOne($user->id);
                $verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['site/verify-email', 'token' => $userSent->email_confirm_token]);
                if (!$this->sentEmailConfirm($userSent)) {
                    $this->addError('id', "Email confirmation is not sent");
                    $transaction->rollBack();
                    return false;
                }
            }

            $transaction->commit();
         //   $transaction->rollBack();
            return true;
        } catch (\Exception $e){
            if (isset($transaction) && $transaction->isActive) {
                $transaction->rollBack();
            }
            $this->addError('id', $e->getMessage());
            return false;
        }
    }

    //************************************************************************************** по Email


    public function sentEmailConfirm($user)
    {
        try{
            $email = $user->email;

            $mailer = Yii::$app->smtpXleMailer;

            $sent = $mailer
                ->compose(
                    ['html' => 'emailVerify-html.php', 'text' => 'emailVerify-text.php'],
                    ['user' => $user])
                ->setTo($email)
                ->setFrom(\Yii::$app->configs->adminEmail)
                ->setSubject('Confirmation of registration')
                ->send();

            if (!$sent) {
                $this->addError('email', 'Ошибка отправки токена');
                return false;
            }
        } catch (\Swift_TransportException $e){
            $this->addError('email', $e->getMessage());
            return false;
        }
        return true;
    }


    public function confirmation($token)
    {
        if (empty($token)) {
            throw new \DomainException('Empty confirm token.');
        }

        $user = User::findOne(['email_confirm_token' => $token]);
        if (!$user) {
            throw new \DomainException('User is not found.');
        }

        $user->email_confirm_token = null;
        $user->status = UserM::STATUS_ACTIVE;

        if (!$user->save()) {
            throw new \RuntimeException('Saving error.');
        }

        if (!\Yii::$app->getUser()->login($user)) {
            throw new \RuntimeException('Error authentication.');
        }

        return true;
    }





}
