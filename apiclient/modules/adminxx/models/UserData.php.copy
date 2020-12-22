<?php

namespace apiadmin\modules\adminxx\models;

use Yii;
use common\helpers\Functions;
use common\models\MainModel;

/**
 * This is the model class for table "user_data".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 *
 * @property User $user
 */
class UserData extends MainModel
{
    public static $activityIntervalArray=[
        0 => 'Увесь час',
        3600 => '1 година',
        7200 => '2 години',
        10800 => '3 години',
        86400 => '1 доби',
        172800 => '2 доби',
        259200 => '3 доби',
        345600 => '4 доби',
    ];

    private $_userLogin;
    private $_lastRoutTime;

    public $activityInterval = 3600;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_data';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'first_name', 'middle_name', 'last_name'], 'required'],
            [['user_id', 'last_rout_time', 'activityInterval'], 'integer'],
            [['emails', 'first_name', 'middle_name', 'last_name'], 'string', 'max' => 255],
            [['last_rout', ], 'string', 'max' => 100],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(),
                'targetAttribute' => ['user_id' => 'id']],
            [['phone'], 'string', 'min' => 6, 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => Yii::t('app', 'Логин'),
            'auth_key' => Yii::t('app', 'Ключ авторизации'),
            'password_hash' => Yii::t('app', 'Пароль'),
            'password_reset_token' => Yii::t('app', 'Токен сброса пароля'),
            'email' => Yii::t('app', 'Email'),
            'status' => Yii::t('app', 'Статус'),
            'created_at' => Yii::t('app', 'Создано'),
            'updated_at' => Yii::t('app', 'Изменено'),
            'refresh_permissions' => Yii::t('app', 'Необходимо обновление разоешений'),
            'invitation' => Yii::t('app', 'С приглашением по Email'),
            'userRolesToSet' => Yii::t('app', 'Роли'),

            //-- user_data
            'first_name' => Yii::t('app', 'Имя'),
            'middle_name' => Yii::t('app', 'Отчество'),
            'last_name' => Yii::t('app', 'Фамилия'),
            'phone' => Yii::t('app', 'Телефон'),
            'last_rout' => Yii::t('app', 'Последний роут'),
            'last_rout_time' => Yii::t('app', 'Последняя активность'),

            //---- служебные
            'password' => Yii::t('app', 'Пароль'),
            'oldPassword' => Yii::t('app', 'Старый пароль'),
            'retypePassword' => Yii::t('app', 'Подтверждение пароля'),

            //----  геттеры
            'created_at_str' => Yii::t('app', 'Создано'),
            'updated_at_str' => Yii::t('app', 'Изменено'),


            'lastRoutTime' => 'Остання активність',
            'userLogin' => 'Логін',
            'activityInterval' => 'Змінити інтервал',
        ];
    }


//*********************************************************************************************** ДАННЫЕ СВЯЗАННЫХ ТАБЛИЦ
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getUserM()
    {
        return $this->hasOne(UserM::class, ['id' => 'user_id']);
    }

    public function getUserDepartments()
    {
        return $this->hasMany(UserDepartment::className(), ['user_id' => 'user_id']);
    }

//*********************************************************************************************** ГЕТТЕРЫ-СЕТТЕРЫ

    public function getUserLogin()
    {
        $this->_userLogin = $this->user->username;
        return $this->_userLogin;
    }

    public function getLastRoutTime()
    {
        $this->_lastRoutTime = Functions::intToDateTime($this->last_rout_time);
        return $this->_lastRoutTime;
    }
}
