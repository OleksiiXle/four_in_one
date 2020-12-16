<?php

namespace apiadmin\modules\adminxx\models\form;

use common\models\UserM;
use Yii;
use yii\base\Model;
use yii\httpclient\Client;

/**
 * Password reset form
 */
class ForgetPassword extends Model
{
    const USER_PASSWORD_PATTERN       = '/^[a-zA-Z0-9_]+$/ui'; //--маска для пароля
    const USER_PASSWORD_ERROR_MESSAGE = 'Припустимі символи - латиниця та цифри'; //--сообщение об ошибке

    public $username;
    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'email'],

            [['username', ], 'string', 'min' => 5, 'max' => 32],
            [['username',  ], 'match', 'pattern' => self::USER_PASSWORD_PATTERN,
                'message' => self::USER_PASSWORD_ERROR_MESSAGE],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Логін',
            'email' => 'Email',
        ];
    }


    /**
     * Send new password
     * @return boolean
     */
    public function forgetPassword()
    {
        $r=1;
        if (!empty($this->email) && !empty($this->username)){
            $user = UserM::find()
                ->where(['email' => $this->email, 'username' => $this->username ])
                ->andWhere(['status' => UserM::STATUS_ACTIVE])
                ->one();
        } elseif (!empty($this->email)){
            $user = UserM::find()
                ->where(['email' => $this->email, ])
                ->andWhere(['status' => UserM::STATUS_ACTIVE])
                ->one();
        } elseif (!empty($this->username)){
            $user = UserM::find()
                ->where([ 'username' => $this->username ])
                ->andWhere(['status' => UserM::STATUS_ACTIVE])
                ->one();
        } else{
            $this->addError('email', 'Введіть email або логін');
            return false;
        }

        if (empty($user)){
            $this->addError('email', 'Користувача не знайдено');
            return false;
        }

        $data = [
            'email' => $user->email,
            'username' => $user->username,
        ];

        //Це якійсь ідентифікатор, який дає можливість працювати ыз зовнішным сайтом
        $pwd = '$2y$13$FFVGvb489vVZKCz7HLl.Re0qSFBUxpPnjA82ryL5TxzD0YxywOELG';

        $urlString = Yii::$app->params['protocol']
            . Yii::$app->params['requestVnzHost']
            . Yii::$app->params['requestRootDir']
            . '/index.php?r=site/contact_to_user&pwd='
            . $pwd;
        $post_string = json_encode($data);

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl($urlString)
            ->setData(['data' => $post_string])
            ->send();

        $pwd = $response->content;

        if($pwd){
            $user->newPassword = $pwd;
            $user->resetPassword();
        }

        return $response->isOk;
    }

}
