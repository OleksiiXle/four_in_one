<?php
namespace app\modules\adminxx\models\form;

use Yii;
use common\models\UserM;
use common\models\UserData;

/**
 * Signup form
 */
class Signup extends UserM
{
    const SCENARIO_CREATE  = 'create';
    const SCENARIO_UPDATE  = 'update';
    const SCENARIO_ACTIVATE  = 'activate';
    const SCENARIO_DEACTIVATE  = 'deactivate';

    public $reCaptcha;

    public function scenarios()
    {
        $ret = parent::scenarios();
        $ret[self::SCENARIO_CREATE] = [
            'username' , 'email', 'first_name', 'middle_name', 'last_name', 'password', 'retypePassword',
            'status', 'phone'
        ];
        $ret[self::SCENARIO_UPDATE] = [
            'username' , 'email', 'first_name', 'middle_name', 'last_name',
            'phone',
        ];
        $ret[self::SCENARIO_ACTIVATE] = [
            'status'
        ];
        $ret[self::SCENARIO_DEACTIVATE] = [
            'status'
        ];
        return $ret ;
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username' , 'email', 'first_name', 'middle_name', 'last_name', 'password', 'retypePassword'], 'required',
                'on' => self::SCENARIO_CREATE],
            [['username' , 'email', 'first_name', 'middle_name', 'last_name'], 'required',
                'on' => self::SCENARIO_UPDATE],
            [['status' ], 'required', 'on' => self::SCENARIO_ACTIVATE],
            [['status' ], 'required', 'on' => self::SCENARIO_DEACTIVATE],

            //---------------------------------------------------------- user
            [['username'], 'string', 'max' => 20],
            [['email'], 'string', 'max' => 100],
            [['status'], 'integer'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            ['email', 'email'],

            //---------------------------------------------------------- user_data
            [['first_name', 'middle_name', 'last_name', 'phone',
                ], 'string', 'max' => 255],

            //----------------------------------------------------------- служебные
            [['retypePassword', ], 'string'],
            [['retypePassword'], 'compare', 'compareAttribute' => 'password'],


            //-------------------------- patterns
            [['username', 'password', 'oldPassword', 'retypePassword',  'newPassword'], 'match', 'pattern' => self::USER_PASSWORD_PATTERN,
                'message' => self::USER_PASSWORD_ERROR_MESSAGE],
            [['first_name', 'middle_name', 'last_name'],  'match', 'pattern' => self::USER_NAME_PATTERN,
                'message' => self::USER_NAME_ERROR_MESSAGE],
            ['phone',  'match', 'pattern' => self::USER_PHONE_PATTERN,
                'message' => self::USER_PHONE_ERROR_MESSAGE],
            //------------------------------------------------------------------------ УНИКАЛЬНОСТЬ
            ['username', 'unique', 'targetClass' => 'common\models\User', 'on' => self::SCENARIO_CREATE],
            ['email', 'unique', 'targetClass' => 'common\models\User', 'on' => self::SCENARIO_CREATE],
        ];
    }

    /**
     * @return boolean
     */
    public function signup()
    {
        $create = $this->isNewRecord;
        $multyFild =json_decode($this->multyFild, true);
    //    return false;
        try {
            $transaction = \Yii::$app->db->beginTransaction();
            //-- создание пользователя
            if ($create){
                $this->setPassword($this->password);
                $this->generateAuthKey();
                $this->status = UserM::STATUS_ACTIVE;
            }
            if ($this->save()){
                if ($create){
                    $userData = new UserData();
                    $userData->setAttributes((array) $this);
                    $userData->user_id = $this->id;
                } else {
                    $userData = UserData::find()
                        ->where(['user_id' => $this->id])->one();
                    $userData->setAttributes((array) $this);
                }
                if ($userData->save()){
                    $transaction->commit();
                    return true;
                } else {
                    $this->addErrors($userData->getErrors());
                }
            }
            $transaction->rollBack();
            return false;

        } catch (\Exception $e){
            if (isset($transaction) && $transaction->isActive) {
                $transaction->rollBack();
            }
            $this->addError('id', $e->getMessage());
            return false;

        }
    }

    public function activate()
    {
        $this->scenario = self::SCENARIO_ACTIVATE;
        $this->status = UserM::STATUS_ACTIVE;
        return $this->save();
    }

    public function deactivate()
    {
        $this->scenario = self::SCENARIO_DEACTIVATE;
        $this->status = UserM::STATUS_INACTIVE;
        return $this->save();
    }
}
