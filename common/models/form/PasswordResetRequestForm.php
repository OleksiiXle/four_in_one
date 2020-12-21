<?php
namespace common\models\form;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $email;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => '\common\models\User',
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => Yii::t('app', 'Информация не найдена')
            ],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public function sendEmail()
    {
        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email' => $this->email,
        ]);

        if (!$user) {
            return false;
        }
        
        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
            if (!$user->save()) {
                return false;
            }
        }

        try{
            $email = $user->email;

            $mailer = Yii::$app->smtpXleMailer;

            $sent = $mailer
                ->compose(
                    ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
                    ['user' => $user])
                ->setTo($email)
                ->setFrom(\Yii::$app->configs->adminEmail)
                ->setSubject('Password reset for ' . Yii::$app->name)
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
}
