<?php

namespace apiadmin\modules\adminxx\grids\filters;

use Yii;
use apiadmin\modules\adminxx\models\UControl;
use apiadmin\modules\adminxx\models\UserData;
use apiadmin\modules\adminxx\models\UserM;
use common\widgets\xgrid\models\GridFilter;

class UControlFilter extends GridFilter
{
    const IP_PATTERN       = '/^[0-9 .]+$/ui'; //--маска для пароля
    const IP_ERROR_MESSAGE = 'Допустиные символы - цифры и точка'; //--сообщение об ошибке
    public $queryModel = UControl::class;

    public $user_id;
    public $remote_ip;
    public $username;
    public $userFam; //last_name

    public $activityInterval;

    public $showAll = "1";
    public $showGuests = "0";
    public $showUsers = "0";

    public $ipWithoutUser = "0";



    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $ownRules = [
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

        return array_merge(parent::rules(), $ownRules);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => Yii::t('app', 'ИД пользователя'),
            'remote_ip' => 'IP',
            'username' => Yii::t('app', 'Логин'),
            'referrer' => 'Referrer',
            'remote_host' => 'Remote Host',
            'absolute_url' => 'Absolute Url',
            'url' => Yii::t('app', 'Последний роут'),
            'created_at' => Yii::t('app', 'Первое посещение'),
            'updated_at' => Yii::t('app', 'Последнее посещение'),
            'createdAt' => Yii::t('app', 'Первое посещение'),
            'updatedAt' => Yii::t('app', 'Последнее посещение'),

            'showAll' => Yii::t('app', 'Все'),
            'showGuests' => Yii::t('app', 'Гости'),
            'showUsers' => Yii::t('app', 'Зарегистрированные'),
            'ipWithoutUser' => Yii::t('app', 'Гости'),
            'activityInterval' => Yii::t('app', 'Время активности'),
            'userFam' => Yii::t('app', 'Фамилия'),

        ];
    }

    public function getCustomQuery()
    {
        $tmp = 1;
        $query = UControl::find()
            ->joinWith(['userDatas'])
            ->joinWith(['users']);

        return $query;
    }


    public function getQuery()
    {
        $query = $this->defaultQuery;

        //---------------------------------------------------------------------------------- USER

        if (!empty($this->username)){
            $query->andWhere(['LIKE', 'user.username', $this->username ]);
            $this->_filterContent .= Yii::t('app', 'Логин') . $this->username . '; ' ;
        }

        //---------------------------------------------------------------------------------- USER_DATA

        if (!empty($this->userFam)){
            $query->andWhere(['LIKE', 'user_data.last_name', $this->userFam ]);
            $this->_filterContent .= Yii::t('app', 'Фамилия') . $this->userFam . '; ' ;
        }

        //---------------------------------------------------------------------------------- U_CONTROL

        if (!empty($this->remote_ip)){
            $query->andWhere(' u_control.remote_ip LIKE "' . $this->remote_ip . '%"');
            $this->_filterContent .= 'IP ' . $this->remote_ip . '; ' ;
        }

        if ($this->showGuests =='1'){
            $query->andWhere(['u_control.user_id' => 0]);
            $this->_filterContent .= Yii::t('app', 'Гости') . '; ' ;
        }

        if ($this->showUsers =='1'){
            $query->andWhere(['>', 'u_control.user_id', 0]);
            $this->_filterContent .= Yii::t('app', 'Зарегистрированные') . '; ' ;
        }

        if ($this->ipWithoutUser =='1'){
            $query->leftJoin('u_control uc2', 'u_control.remote_ip = uc2.remote_ip AND uc2.user_id > 0')
                ->where(['uc2.id' => null])
            ;
            $this->_filterContent .= Yii::t('app', 'Гости') . '; ' ;
        }

        if (!empty($this->activityInterval)){
            $query->andWhere(['>', 'u_control.updated_at', (time() - $this->activityInterval)]);
            $this->_filterContent .= Yii::t('app', 'Активность') . UserData::$activityIntervalArray[$this->activityInterval] . '; ' ;
        }
       //   $r = $query->createCommand()->getSql();

        return $query;
    }

    /**
     * Определение столбцов для вывода в файл
     * @return array
     */
    public function getDataForUpload()
    {
        //-- пример:
        return [
            'user_id' => [
                'label' => 'user_id',
                'content' => 'value'
            ],
            'remote_ip' => [
                'label' => 'remote_ip',
                'content' => 'value'
            ],
            'username' => [
                'label' => 'username',
                'content' => 'value'
            ],
            'callBack' => [ //-- в столбец выводится значение function с подписью сверху label
                'label' => 'user',
                'content' => function($model)
                {
                    return (isset($model->userDatas)) ? $model->userDatas->last_name: '';
                }
            ],

            'createdAt' => [
                'label' => 'createdAt',
                'content' => 'value'
            ],
            'updatedAt' => [
                'label' => 'updatedAt',
                'content' => 'value'
            ],
            'url' => [
                'label' => 'url',
                'content' => 'value'
            ],
        ];
    }

}