<?php
namespace app\modules\adminxx\models\filters;

use app\commands\backgroundTasks\models\BackgroundTask;
use app\helpers\Functions;
use app\modules\adminxx\models\UserM;
use app\widgets\xlegrid\models\GridFilter;

class UserFilter extends GridFilter
{
    public $queryModel = UserM::class;

    public $datetime_range = '';
    public $datetime_min = '';
    public $datetime_max = '';

    public $id;
    public $first_name;
    public $middle_name;
    public $last_name;
    public $username;
    public $emails;

    public $role;
    public $permission;
    private $_roleDict;

    /**
     * @return mixed
     */
    public function getRoleDict()
    {
        $roles = \Yii::$app->authManager->getRoles();
        $this->_roleDict['0'] = 'Не визначено';
        foreach ($roles as $role){
            $this->_roleDict[$role->name] = $role->name;
        }

        return $this->_roleDict;
    }
    public $permissionDict;
    public $additionalTitle = '';

    public $showStatusActive;
    public $showStatusInactive;


    public function getFilterContent()
    {
        if ($this->_filterContent === null) {
            $this->getQuery();
        }

        return $this->_filterContent;
    }

    public function rules()
    {
        return [
            [[ 'showStatusActive', 'showStatusInactive', 'showOnlyChecked'], 'boolean'],
            [['first_name', 'middle_name', 'last_name', 'role', 'username', 'emails'], 'string', 'max' => 50],
            [['checkedIdsJSON'], 'string', 'max' => 1000],
            [['first_name', 'middle_name', 'last_name'],  'match', 'pattern' => UserM::USER_NAME_PATTERN,
                'message' => \Yii::t('app', UserM::USER_NAME_ERROR_MESSAGE)],
            [['username'],  'match', 'pattern' => UserM::USER_PASSWORD_PATTERN,
                'message' => \Yii::t('app', UserM::USER_PASSWORD_ERROR_MESSAGE)],
            [['id', ], 'integer'],
            [['first_name', 'middle_name', 'last_name', 'role'], 'string', 'max' => 50],
            [['datetime_range', 'datetime_min', 'datetime_max'], 'string', 'max' => 100],
            ['emails', 'email'],
           // [['datetime_range'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],



        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Логін',
            'first_name' => 'Імя',
            'middle_name' => 'По батькові',
            'last_name' => 'Прізвище',
            'showOnlyChecked' => 'Только выбранные',
            'datetime_range' => 'Создан',

            'phone' => 'Телефон',
            'auth_key' => 'Ключ авторізації',
            'password' => 'Пароль',
            'password_hash' => 'Пароль',
            'oldPassword' => 'Старий пароль',
            'retypePassword' => 'Підтвердждення паролю',
            'password_reset_token' => 'Токен збросу паролю',
            'emails' => 'Email',
            'status' => 'Status',
            'created_at_str' => 'Створений',
            'updated_at_str' => 'Змінений',
            'time_login_str' => 'Увійшов',
            'time_logout_str' => 'Вийшов',
            'role' => 'Роль користувача',
            'showStatusAll' => 'Всі',
            'showStatusActive' => 'Активні',
            'showStatusInactive' => 'Не активні',
        ];
    }

    public function getQuery()
    {
        $tmp = 1;
        $query = UserM::find()
            ->joinWith(['userDatas']);
        $this->_filterContent = '';
        if ($this->showOnlyChecked =='1' && !empty($this->checkedIdsJSON)) {
            $checkedIds = json_decode($this->checkedIdsJSON);
            $query->andWhere(['IN', 'user.id', $checkedIds]);
            $this->_filterContent .= ' * Только отмеченные*;' ;
            return $query;
        }

        if (!$this->validate()) {
            return $query;
        }

        if (!empty($this->role)) {
            $query ->innerJoin('auth_assignment aa', 'user.id=aa.user_id')
                ->innerJoin('auth_item ai', 'aa.item_name=ai.name')
                ->where(['ai.type' => 1])
                ->andWhere(['aa.item_name' => $this->role])
            ;
            $this->_filterContent .= ' Роль *' . $this->roleDict[$this->role] . '*;' ;
        }

        if (!empty($this->emails)) {
            $query->andWhere(['LIKE', 'user.emails', $this->emails]);
            $this->_filterContent .= ' Email *' . $this->emails . '*;' ;
        }

        if (!empty($this->username)) {
            $query->andWhere(['user.username' => $this->username]);
            $this->_filterContent .= ' Логін *' . $this->username . '*;' ;
        }


        if (!empty($this->first_name)) {
            $query->andWhere(['like', 'user_data.first_name', $this->first_name]);
            $this->_filterContent .= ' Ім"я *' . $this->first_name . '*;' ;
        }

        if (!empty($this->middle_name)) {
            $query->andWhere(['like', 'user_data.middle_name', $this->middle_name]);
            $this->_filterContent .= ' По-батькові *' . $this->middle_name . '*;' ;
        }

        if (!empty($this->last_name)) {
            $query->andWhere(['like', 'user_data.last_name', $this->last_name]);
            $this->_filterContent .= ' Прізвище *' . $this->last_name . '*;' ;
        }

        if ($this->showStatusActive =='1'){
            $query->andWhere(['user.status' => UserM::STATUS_ACTIVE]);
            $this->_filterContent .= ' * Тількі активні*;' ;
        }

        if ($this->showStatusInactive =='1'){
            $query->andWhere(['user.status' => UserM::STATUS_INACTIVE]);
            $this->_filterContent .= ' * Тількі неактивні*;' ;
        }

        if (!empty($this->datetime_min) && !empty($this->datetime_max)) {
            $tmp = strtotime($this->datetime_min);
            $query->andWhere(['>=','user.created_at', strtotime($this->datetime_min)])
                  ->andWhere(['<=','user.created_at', strtotime($this->datetime_max)]);
            $this->_filterContent .= ' * Создан ' . $this->datetime_range . ' *;' ;
        }


        $e = $query->createCommand()->getSql();

        return $query;


    }

    public function getDataForUpload()
    {
        return [
            'username' => [
                'label' => 'Логін',
                'content' => 'value'
            ],
            'status' => [
                'label' => 'Статус',
                'content' => function($model)
                {
                    return ($model->status == UserM::STATUS_ACTIVE) ? 'active' : 'not active';
                }
            ],
        ];
    }

}