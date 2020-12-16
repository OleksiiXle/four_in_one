<?php
namespace apiadmin\modules\adminxx\models\form;

use common\models\UserM;
use common\models\UserData;


/**
 * Update form
 */
class Update extends UserM
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['email', 'filter', 'filter' => 'trim'],
            //----------------------------------------------------------------------- ТИПЫ ДАННЫХ, РАЗМЕР
            [['created_at', 'updated_at',], 'integer'],

            ['rememberMe', 'boolean'],
            ['email', 'email'],

            [['first_name', 'middle_name', 'last_name', 'email', ], 'string', 'max' => 255],


            //------------------------------------------------------------------------ МАСКИ ВВОДА
            [['first_name', 'middle_name', 'last_name'],  'match', 'pattern' => self::USER_NAME_PATTERN,
                'message' => \Yii::t('app', self::USER_NAME_ERROR_MESSAGE)],
        ];
    }

    /**
     * @return boolean
     */
    public function updateUser()
    {
        if ($this->save()) {
            $userData = UserData::findOne(['user_id' => $this->id]);
            $userData->setAttributes($this->getAttributes());
            $userData->first_name = $this->first_name;
            $userData->middle_name = $this->middle_name;
            $userData->last_name = $this->last_name;

            if (!$userData->save()){
                $this->addErrors($userData->getErrors());
                return false;
            } else {
                return true;
            }
        }

        return false;
    }


}
