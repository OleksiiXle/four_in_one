<?php
namespace common\models\form;

use common\models\UserM;
use yii\base\InvalidArgumentException;
use yii\base\Model;
use Yii;
use common\models\User;

/**
 * Password reset form
 */
class ResetPasswordForm extends Model
{
    public $newPassword;
    public $retypePassword;
    private $token;

    /**
     * @var \common\models\User
     */
    private $_user;


    /**
     * Creates a form model given a token.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidArgumentException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Password reset token cannot be blank.');
        }
        $this->_user = User::findByPasswordResetToken($token);
        $this->token = $token;
        if (!$this->_user) {
            throw new InvalidArgumentException('Wrong password reset token.');
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['newPassword', 'retypePassword'], 'required'],
            [['retypePassword', 'newPassword'],  'string', 'min' => 3, 'max' => 20],
            [['retypePassword'], 'compare', 'compareAttribute' => 'newPassword'],
            [['retypePassword',  'newPassword' ], 'match', 'pattern' => UserM::USER_PASSWORD_PATTERN,
                'message' => \Yii::t('app', UserM::USER_PASSWORD_ERROR_MESSAGE)],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'newPassword' => Yii::t('app', 'Новый пароль'),
            'retypePassword' => Yii::t('app', 'Подтверждение'),
        ];
    }

    /**
     * Change password.
     *
     * @return boolean
     */
    public function change()
    {
        if ($this->validate()) {
            /* @var $user User */
            $user =  User::findByPasswordResetToken($this->token);
            $user->setPassword($this->newPassword);
            $user->generateAuthKey();
            $user->removePasswordResetToken();
            if ($user->save()) {
                return true;
            }
        }

        return false;
    }

}
