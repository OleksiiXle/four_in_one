<?php
namespace apiadmin\modules\adminxx\models\filters;

use apiadmin\modules\adminxx\models\UControl;
use apiadmin\modules\adminxx\models\UserData;
use apiadmin\modules\adminxx\models\UserM;
use yii\base\Model;

class UControlFilter extends Model
{
    const IP_PATTERN       = '/^[0-9 .]+$/ui'; //--маска для пароля
    const IP_ERROR_MESSAGE = 'Допустиные символы - цифры и точка'; //--сообщение об ошибке

    public $user_id;
    public $remote_ip;
    public $username;
    public $userFam; //last_name

    public $activityInterval;

    public $showAll = "1";
    public $showGuests = "0";
    public $showUsers = "0";

    public $ipWithoutUser = "0";

    private $_filterContent;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activityInterval', ], 'integer'],
            ['remote_ip', 'filter', 'filter' => 'trim'],
            ['username', 'filter', 'filter' => 'trim'],

            [['user_id'], 'integer'],
            [['remote_ip', 'username'], 'string', 'max' => 32],
            [['remote_ip',],  'match', 'pattern' => self::IP_PATTERN,
                'message' => \Yii::t('app', \Yii::t('app', self::IP_ERROR_MESSAGE))],
            [['username', ], 'match', 'pattern' => UserM::USER_PASSWORD_PATTERN,
                'message' => \Yii::t('app', UserM::USER_PASSWORD_ERROR_MESSAGE)],
            [[ 'showAll', 'showGuests', 'showUsers', 'ipWithoutUser'], 'boolean'],
            [['userFam'],  'match', 'pattern' => UserM::USER_NAME_PATTERN,
                'message' => UserM::USER_NAME_ERROR_MESSAGE],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [

            'user_id' => 'User ID',
            'remote_ip' => 'IP',
            'username' => 'Логін',
            'showAll' => 'Всі',
            'showGuests' => 'Відвідувачі',
            'showUsers' => 'Зареєстровані користувачі',
            'ipWithoutUser' => 'Сторонні відвідувачі',
            'activityInterval' => 'Час активності',
            'userFam' => 'Прізвище',

        ];
    }

    public function getQuery()
    {
        $r=1;
        $query = UControl::find()
            ->alias('uc')
            ->leftJoin('user_data', 'uc.user_id=user_data.user_id')
        ;


        //---------------------------------------------------------------------------------- USER

        if (!empty($this->username)){
            $query->leftJoin('user', 'uc.user_id=user.id')
                ->andWhere(['LIKE', 'user.username', $this->username ]);
        }

        //---------------------------------------------------------------------------------- USER_DATA

        if (!empty($this->userFam)){
            $query->andWhere(['LIKE', 'user_data.last_name', $this->userFam ]);
        }

        //---------------------------------------------------------------------------------- U_CONTROL

        if (!empty($this->remote_ip)){
            $query->andWhere(' uc.remote_ip LIKE "' . $this->remote_ip . '%"');
        }

        if ($this->showGuests =='1'){
            $query->andWhere(['uc.user_id' => 0]);
        }

        if ($this->showUsers =='1'){
            $query->andWhere(['>', 'uc.user_id', 0]);
        }



        if ($this->ipWithoutUser =='1'){
            $query->leftJoin('u_control uc2', 'uc.remote_ip = uc2.remote_ip AND uc2.user_id > 0')
                ->where(['uc2.id' => null])
            ;
        }

        if (!empty($this->activityInterval)){
            $query->andWhere(['>', 'uc.updated_at', (time() - $this->activityInterval)]);
        }


          $r = $query->createCommand()->getSql();

        return $query;
    }

    public function getFilterContent(){
        $this->_filterContent = '';


        if (!empty($this->userFam)){
            $this->_filterContent .= ' Прізвище *' . $this->userFam . '*;' ;
        }

        if (!empty($this->activityInterval)){
            $this->_filterContent .= '  Активність *' . UserData::$activityIntervalArray[$this->activityInterval] . '*;' ;
        }


        if (!empty($this->remote_ip)) {
            $this->_filterContent .= ' IP *' . $this->remote_ip . '*;' ;
        }

        if (!empty($this->username)) {
            $this->_filterContent .= ' Логін *' . $this->username . '*;' ;
        }

        if ($this->showGuests =='1'){
            $this->_filterContent .= ' * Тількі Відвідувачі*;' ;
        }

        if ($this->showUsers =='1'){
            $this->_filterContent .= ' * Тількі Зареєстровані користувачі*;' ;
        }

        if ($this->ipWithoutUser =='1'){
            $this->_filterContent = ' * Сторонні відвідувачі *;' ;
        }


        return $this->_filterContent;
    }

}