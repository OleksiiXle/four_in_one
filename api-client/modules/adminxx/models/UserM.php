<?php

namespace app\modules\adminxx\models;

use app\models\UserToken;
use Yii;
use app\models\MainModel;
use common\helpers\Functions;

/**
 * User - модель с правилами, геттерами, сеттерами и пр. данными
 *
 */
class UserM extends MainModel
{
    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 0;
    const STATUS_WAITING_FOR_EMAIL_CONFIRM = 1;
    const STATUS_WAITING_FOR_INVITATION_CONFIRM = 2;

    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => 'Активный',
            self::STATUS_INACTIVE => Yii::t('app', 'Деактивирован'),
            self::STATUS_WAITING_FOR_EMAIL_CONFIRM => Yii::t('app', 'Ожидает подтверждения регистрации по Email'),
            self::STATUS_WAITING_FOR_INVITATION_CONFIRM => Yii::t('app', 'Ожидается подтверждение приглашения по Email'),
        ];
    }

    const SCENARIO_SIGNUP_BY_ADMIN  = 'signup_by_admin';
    const SCENARIO_CONFIRM_INVITATION  = 'confirm_invitation';
    const SCENARIO_UPDATE  = 'update';
    const SCENARIO_ACTIVATE  = 'activate';
    const SCENARIO_DEACTIVATE  = 'deactivate';
/*
    const USER_NAME_PATTERN           = '/^[А-ЯІЇЄҐ]{1}[а-яіїєґ0-9_ \']+([-]?[А-ЯІЇЄҐ]{1}[а-яіїєґ0-9 \']+)?$/u'; //--маска для нимени
    const USER_NAME_ERROR_MESSAGE     = 'Використовуйте українські літери, починаючи із великої. 
                                         Апостроф - в англійській розкладці на букві є. Подвійні імена - через тире!'; //--сообщение об ошибке
*/
    const USER_NAME_PATTERN           = '/^[А-ЯІЇЄҐа-яіїєґA-Za-z0-9\']+?$/u'; //--маска для нимени
    const USER_NAME_ERROR_MESSAGE     = 'Допустимы буквы. Двойные имена через тире'; //--сообщение об ошибке
    const USER_PASSWORD_PATTERN       = '/^[a-zA-Z0-9~!@#$%^&*()_-]+$/ui'; //--маска для пароля
    const USER_PASSWORD_ERROR_MESSAGE = 'Допустимы буквы, цифры, спецсимволы ~!@#$%^&*()_-'; //--сообщение об ошибке
    const USER_PHONE_PATTERN       = '/^[0-9, \-)(+]+$/ui'; //--маска для телефона
    const USER_PHONE_ERROR_MESSAGE = 'Допустимые символы - цифры, скобки, тире, +'; //--сообщение об ошибке


    public $first_name;
    public $middle_name;
    public $last_name;
    public $phone;

    public $password;
    public $retypePassword;
    public $oldPassword;
    public $newPassword;
    public $rememberMe = true;
    public $invitation = 0;
    public $userRolesToSet;

    private $_user = false;
    private $_created_at_str;
    private $_updated_at_str;
    private $_userRoles;
    private $_nameFam; //-- фамилия
    private $_nameNam;//--имя
    private $_nameFat;//-- отчество

    private $_userProfile;
    private $_userProfileStrFull;
    private $_userProfileStrShort;
    private $_userCreater;
    private $_userUpdater;

    private $_firstVisitTime;

    private $_lastVisitTime;
    private $_firstVisitTimeTxt;
    private $_lastVisitTimeTxt;
    private $_lastRoute;

    private $_apiLoginsInfo;

    public static function tableName()
    {
        return 'user';
    }

    public function scenarios()
    {
        $ret = parent::scenarios();
        $ret[self::SCENARIO_SIGNUP_BY_ADMIN] = [
            //------------------------------------------------------------------------- user
            'username', 'email', 'status',
            'created_at', 'updated_at', 'created_by', 'updated_by', 'password_hash',
            'password', 'retypePassword', 'password_reset_token', 'auth_key', 'rememberMe', 'invitation',
            'userRolesToSet',
            //------------------------------------------------------------------------- user_data
            'first_name', 'middle_name', 'last_name', 'phone',
        ];
        $ret[self::SCENARIO_UPDATE] = [
            'first_name', 'middle_name', 'last_name', 'phone', 'status',
             'userRolesToSet',
        ];
        $ret[self::SCENARIO_ACTIVATE] = [
            'status'
        ];
        $ret[self::SCENARIO_DEACTIVATE] = [
            'status'
        ];
        $ret[self::SCENARIO_CONFIRM_INVITATION] = [
        ];
        return $ret ;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        switch ($this->scenario) {
            case self::SCENARIO_SIGNUP_BY_ADMIN:
                return [
                    //------------------------------------------------------------------------- user
                    [['username' , 'email', 'password', 'retypePassword'], 'required'],
                    [['username', 'email'], 'filter', 'filter' => 'trim'],
                    [['status', 'created_at', 'updated_at', 'created_by', 'updated_by', 'invitation',], 'integer'],
                    [['password_hash', 'password_reset_token', 'email' ], 'string', 'max' => 255],
                    ['rememberMe',  'boolean'],
                    ['email', 'email'],
                    [['username', 'auth_key'], 'string', 'min' => 5, 'max' => 32],
                    [['password', 'retypePassword', 'oldPassword' , 'newPassword'],  'string', 'min' => 3, 'max' => 20],
                    ['username', 'validateUsername'],
                    ['email', 'validateEmail'],
                    [['username', 'password', 'oldPassword', 'retypePassword',  'newPassword' ], 'match', 'pattern' => self::USER_PASSWORD_PATTERN,
                        'message' => Yii::t('app', self::USER_PASSWORD_ERROR_MESSAGE)],
                    [['status'], 'default', 'value' => self::STATUS_ACTIVE],

                    //------------------------------------------------------------------------- user_data
                    [['first_name', 'last_name'], 'required',],
                    [['first_name', 'middle_name', 'last_name', 'phone', 'email' ], 'string', 'max' => 255],
                    [['first_name', 'middle_name', 'last_name'],  'match', 'pattern' => self::USER_NAME_PATTERN,
                        'message' => Yii::t('app', self::USER_NAME_ERROR_MESSAGE)],
                    ['phone',  'match', 'pattern' => self::USER_PHONE_PATTERN,
                        'message' => Yii::t('app', self::USER_PHONE_ERROR_MESSAGE)],
                ];
            case self::SCENARIO_UPDATE:
                return [
                    //------------------------------------------------------------------------- user_data
                    [['first_name', 'last_name'], 'required',],
                    [['first_name', 'middle_name', 'last_name', 'phone'], 'string', 'max' => 255],
                    [['first_name', 'middle_name', 'last_name'],  'match', 'pattern' => self::USER_NAME_PATTERN,
                        'message' => Yii::t('app', self::USER_NAME_ERROR_MESSAGE)],
                    ['phone',  'match', 'pattern' => self::USER_PHONE_PATTERN,
                        'message' => Yii::t('app', self::USER_PHONE_ERROR_MESSAGE)],
                ];
            case self::SCENARIO_ACTIVATE:
            case self::SCENARIO_DEACTIVATE:
            case self::SCENARIO_CONFIRM_INVITATION:
            return [
                    //------------------------------------------------------------------------- user
                    [['status', 'updated_at', 'updated_by',], 'integer'],
                ];
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            //-- user
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

        ];
    }

    public function validateEmail()
    {
        $checkCondition = ($this->isNewRecord) ? ['email' => $this->email] : 'email = "' . $this->email . '" AND id != ' . $this->id;
        $check = self::find()->where($checkCondition)->count();
        if (!empty($check)) {
            $this->addError('email', Yii::t('app', 'Email уже занято'));
        }
    }

    public function validateUsername()
    {
        $checkCondition = ($this->isNewRecord) ? ['username' => $this->username] : 'username = ' . $this->username . ' AND id != ' . $this->id;
        $check = self::find()->where($checkCondition)->count();
        if (!empty($check)) {
            $this->addError('username', Yii::t('app', 'Логин уже занято'));
        }
    }

//*********************************************************************************************** ДАННЫЕ СВЯЗАННЫХ ТАБЛИЦ

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserDatas()
    {
        return $this->hasOne(UserData::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUControl()
    {
        return $this->hasOne(UControl::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserToken()
    {
        return $this->hasMany(UserToken::class, ['client_id' => 'id']);
    }

//*********************************************************************************************** ГЕТТЕРЫ-СЕТТЕРЫ

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }

    public function getNameFam()
    {
        $this->_nameFam = (isset($this->userDatas->last_name)) ? $this->userDatas->last_name : '';
        return $this->_nameFam;
    }

    public function getNameNam()
    {
        $this->_nameNam = (isset($this->userDatas->first_name)) ? $this->userDatas->first_name : '';
        return $this->_nameNam;
    }

    public function getNameFat()
    {
        $this->_nameFat = (isset($this->userDatas->middle_name)) ? $this->userDatas->middle_name : '';
        return $this->_nameFat;
    }

    public function getCreated_at_str()
    {
        $this->_created_at_str = Functions::intToDateTime($this->created_at);
        return $this->_created_at_str;
    }

    public function getUpdated_at_str()
    {
        $this->_updated_at_str = Functions::intToDateTime($this->updated_at);
        return $this->_updated_at_str;
    }

    public function getTime_login_str()
    {
        $this->_time_login_str = Functions::intToDateTime($this->time_login);
        return $this->_time_login_str;
    }

    public function getTime_logout_str()
    {
        $this->_time_logout_str = Functions::intToDateTime($this->time_logout);
        return $this->_time_logout_str;
    }

    public function getTime_session_expire_str()
    {
        $this->_time_session_expire_str = Functions::intToDateTime($this->time_session_expire);
        return $this->_time_session_expire_str;
    }

    public function getUserRoles()
    {
        $this->_userRoles = '';
        $roles = \Yii::$app->authManager->getRolesByUser($this->id);
        if (isset($roles)){
            foreach ($roles as $role){
                $this->_userRoles .= $role->name . ' ';
            }
        }
        return $this->_userRoles;
    }

    public function getUserCreater()
    {
        $this->_userCreater = '';
        $user = self::findOne(['id' => $this->created_by]);
        if (isset($user)){
            $this->_userCreater = $user->username;
        }
        return $this->_userCreater;
    }

    public function getUserUpdater()
    {
        $this->_userUpdater = '';
        $user = self::findOne(['id' => $this->updated_by]);
        if (isset($user)){
            $this->_userUpdater = $user->username;
        }
        return $this->_userUpdater;
    }

    public function getUserProfile()
    {
        $userDatas = $this->userDatas;
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($this->id);
        $permissions = $auth->getPermissionsByUser($this->id);
        $userRoles = [];
        $userPermissions = [];
        $userRoutes = [];
        if (!empty($roles)){
            foreach ($roles as $key => $role){
                $userRoles[] = [
                    'id' => $key,
                    'name' => $role->description,
                ];
            }
        }
        if (!empty($permissions)){
            foreach ($permissions as $key => $permission){
                if (substr($key, 0, 1) == '/'){
                    $userRoutes [] = [
                        'id' => $key,
                        'name' => $role->description,
                    ];
                } else {
                    $userPermissions [] = [
                        'id' => $key,
                        'name' => $role->description,
                    ];
                }
            }
        }
        $userDepartments = $this->userDepartmentsData;

        //---------------------
        $this->_userProfile = [
            'id' => $this->id,
            'status' => $this->status,
            'username' => $this->username,
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'userRoles' => $userRoles,
            'userPermissions' => $userPermissions,
            'userRoutes' => $userRoutes,

            'created_at' => ($this->created_at)
                ? Functions::intToDateTime($this->created_at) : Functions::intToDateTime($this->creation_time),
            'updated_at' => ($this->updated_at)
                ? Functions::intToDateTime($this->updated_at) : Functions::intToDateTime($this->update_time),
            'userUpdater' => $this->userUpdater,
            'userCreater' => $this->userCreater,
            //-- u_control
            'firstVisitTimeTxt' => $this->firstVisitTimeTxt,
            'lastVisitTimeTxt' => $this->lastVisitTimeTxt,
            'lastRoute' => $this->lastRoute,

            //-- user_data
            'personal_id' => (isset($userDatas)) ? $userDatas->personal_id : 0,

            //-- personal
        ];


        return $this->_userProfile;
    }

    /**
     * @return mixed
     */
    public function getUserProfileStrFull()
    {
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($this->id);
        $permissions = $auth->getPermissionsByUser($this->id);
        $userRoles = '';
        $userPermissions = '';
        $userRoutes = '';

        $endStr = PHP_EOL;
        if (!empty($roles)){
            foreach ($roles as $key => $role){
                $userRoles .= $key . ' - ' . $role->description . $endStr;
                if (!empty($endStr)){
                    $endStr = '';
                }
            }
        }

        $endStr = PHP_EOL;
        $endStr1 = PHP_EOL;
        if (!empty($permissions)){
            foreach ($permissions as $key => $permission){
                if (substr($key, 0, 1) == '/'){
                    $userRoutes .= $key . ' - ' . $permission->description . $endStr;
                    if (!empty($endStr)){
                        $endStr = '';
                    }
                } else {
                    $userPermissions .= $key . ' - ' . $permission->description . $endStr1;
                    if (!empty($endStr1)){
                        $endStr1 = '';
                    }
                }
            }
        }

        //---------------------
        $this->_userProfileStrFull = [
            'id' => $this->id,
            'status' => self::STATUS_DICT[$this->status],
            'username' => $this->username,
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'userRoles' => $userRoles,
            'userPermissions' => $userPermissions,
            'userRoutes' => $userRoutes,
            'created_at' => ($this->created_at)
                ? Functions::intToDateTime($this->created_at) : Functions::intToDateTime($this->creation_time),
            'updated_at' => ($this->updated_at)
                ? Functions::intToDateTime($this->updated_at) : Functions::intToDateTime($this->update_time),
            'userUpdater' => $this->userUpdater,
            'userCreater' => $this->userCreater,
            'firstVisitTimeTxt' => $this->firstVisitTimeTxt,
            'lastVisitTimeTxt' => $this->lastVisitTimeTxt,
            'lastRoute' => $this->lastRoute,
        ];

        return $this->_userProfileStrFull;
    }

    /**
     * @return mixed
     */
    public function getUserProfileStrShort()
    {
        $userProfileStrFull = $this->userProfileStrFull;

        $this->_userProfileStrShort = [
            'status' => $userProfileStrFull['status'],
            'username' => $userProfileStrFull['username'],
            'last_name' => $userProfileStrFull['last_name'],
            'first_name' => $userProfileStrFull['first_name'],
            'middle_name' => $userProfileStrFull['middle_name'],
            'email' => $userProfileStrFull['email'],
            'phone' => $userProfileStrFull['phone'],
            'userRoles' => $userProfileStrFull['userRoles'],
        ];

        return $this->_userProfileStrShort;
    }

    /**
     * @return mixed
     */
    public function getFirstVisitTime()
    {
        $this->_firstVisitTime = 0;
        if (isset($this->uControl)){
            $this->_firstVisitTime = $this->uControl->created_at;
        }
        return $this->_firstVisitTime;
    }

    /**
     * @return mixed
     */
    public function getLastVisitTime()
    {
        $this->_lastVisitTime = 0;
        if (isset($this->uControl)){
            $this->_lastVisitTime = $this->uControl->updated_at;
        }
        return $this->_lastVisitTime;
    }

    /**
     * @return mixed
     */
    public function getFirstVisitTimeTxt()
    {
        $this->_firstVisitTimeTxt = '';
        if (isset($this->uControl)){
            $this->_firstVisitTimeTxt = Functions::intToDateTime($this->uControl->created_at);
        }
        return $this->_firstVisitTimeTxt;
    }

    /**
     * @return mixed
     */
    public function getLastVisitTimeTxt()
    {
        $this->_lastVisitTimeTxt = '';
        if (isset($this->uControl)){
            $this->_lastVisitTimeTxt = Functions::intToDateTime($this->uControl->updated_at);
        }
        return $this->_lastVisitTimeTxt;
    }

    /**
     * @return mixed
     */
    public function getLastRoute()
    {
        $this->_lastRoute = '';
        if (isset($this->uControl)){
            $this->_lastRoute = $this->uControl->url;
        }
        return $this->_lastRoute;
    }

    /**
     * @return mixed
     */
    public function getApiLoginsInfo()
    {
        $this->_apiLoginsInfo = '';
        foreach ($this->userToken as $userToken) {
            $expires_in = (int) $userToken->created_at + ((int) $userToken->expires_in);
            $this->_apiLoginsInfo .= Functions::intToDateTime($expires_in) . PHP_EOL;
        }
        return $this->_apiLoginsInfo;
    }

//*********************************************************************************************** CRUD

    /**
     * @return boolean
     */
    public function updateUser()
    {
        $create = $this->isNewRecord;
        $userRoles =json_decode($this->userRolesToSet, true);
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
                    //-------------------------------- назначение роли
                    $auth = Yii::$app->authManager;
                    $userRolesOld = $auth->getRolesByUser($this->id);
                    if (!$create){
                        //--- сброс имевшихся, но удаленных ролей
                        foreach ($userRolesOld as $name => $role){
                            if (!isset($userRoles[$name])){
                                $ret = $auth->revoke($role,$this->id);
                            }
                        }
                    }
                    //-- добавление ролей
                    foreach ($userRoles as $role){
                        $userRole = $auth->getRole($role);
                        if (isset($userRole)){
                            if (!isset($userRolesOld[$role])){
                                $ret = $auth->assign($userRole, $this->id);
                                if (!$ret){
                                    $this->addError('id', "Помилка призначення ролі " . $role['id']);
                                    $transaction->rollBack();
                                    return false;
                                }
                            }
                        } else {
                            $this->addError('id', "Роль " . $role['id'] . " не знайдена");
                            $transaction->rollBack();
                            return false;
                        }
                    }
                    //------------------------------- все ок
                    $transaction->commit();
                  //  $transaction->rollBack();
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

//********************************************************************************* МЕДОТЫ АВТОРИЗАЦИИ И АУТЕНТИФИКАЦИИ
    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, \Yii::t('app', 'Невірний логін або пароль'));
            } elseif ($user->status != self::STATUS_ACTIVE) {
                $this->addError($attribute, 'Ваш статус - ' . self::STATUS_DICT[$user->status]);
            }
        }
    }

    public function validateOldPassword()
    {
        /* @var $user User */
        $user = Yii::$app->user->identity;
        if (!$user || !$user->validatePassword($this->oldPassword)) {
            $this->addError('oldPassword', \Yii::t('app', 'Неверный старый пароль'));
        }
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }


    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

//********************************************************************************** ПЕРЕОПРЕДЕЛЕННЫЕ МЕТОДЫ

    public function afterFind()
    {
        if (isset($this->userDatas)){
            $data = $this->userDatas;
            $this->first_name = $data->first_name;
            $this->middle_name = $data->middle_name;
            $this->last_name = $data->last_name;
            $this->phone = $data->phone;
        }
        parent::afterFind(); // TODO: Change the autogenerated stub
    }



//********************************************************************************** FOR RESET PASSWORD

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * +++ Finds user for reset password
     *
     * @param string $data
     * @return static|null
     */
    public static function findForReset($data)
    {
        //return var_dump(stripos($data, '@'));
        if(stripos($data, '@')){
            return static::findByEmail(trim($data));
        }else{
            return static::findByUsername(trim($data));
        }
    }

    /**
     * Set new Password for user.
     *
     * @return User|false the saved model or null if saving fails
     */
    public function resetPassword() {
        $this->setPassword($this->newPassword);
        $this->generateAuthKey();
        if ($this->save(false)) {
            return true;
        }
        return false;
    }

//********************************************************************************** Другие функции

    public static function getDefaultRoles()
    {
        $result = [];
        if (isset(Yii::$app->params['defaultRoles'])) {
            $defaultRoles = Yii::$app->params['defaultRoles'];
            $auth = Yii::$app->authManager;
            foreach ($defaultRoles as $role) {
                $authRole = $auth->getRole($role);
                if (!empty($authRole)) {
                    $result[$role] = Yii::t('app', $authRole->description);
                }
            }
        }
        return $result;
    }

    public static function getStatusDict(){

        return [
            self::STATUS_INACTIVE => \Yii::t('app', 'Не активный'),
            self::STATUS_WAITING_FOR_EMAIL_CONFIRM => \Yii::t('app', 'Ожидает подтверждения'),
            self::STATUS_WAITING_FOR_INVITATION_CONFIRM => \Yii::t('app', 'Ожидает подтверждения'),
            self::STATUS_ACTIVE => \Yii::t('app', 'Активный'),
        ];

    }
}
