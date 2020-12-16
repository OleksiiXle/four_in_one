<?php

namespace apiadmin\modules\adminxx\models\filters;

use Yii;
use common\models\UserM;
use common\widgets\xgrid\models\GridFilter;

class UserFilterExample extends GridFilter
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

    public $permissionDict;
    public $additionalTitle = '';

    public $showStatusActive;
    public $showStatusInactive;

    public function rules()
    {
        $ownRules = [
            [[ 'showStatusActive', 'showStatusInactive', 'showOnlyChecked'], 'boolean'],
            [['first_name', 'middle_name', 'last_name', 'role', 'username', 'emails'], 'string', 'max' => 50],
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
        return array_merge(parent::rules(), $ownRules);
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

    public function getCustomQuery()
    {
        return UserM::find()
            ->joinWith(['userDatas']);
    }

    public function getQuery()
    {
        //Yii::t('app', 'Пометить все выбранные строки, как выделенные')
        $query = $this->defaultQuery;

        if (!$this->validate()) {
            return $query;
        }

        if (!empty($this->role)) {
            $query ->innerJoin('auth_assignment aa', 'user.id=aa.user_id')
                ->innerJoin('auth_item ai', 'aa.item_name=ai.name')
                ->andWhere(['ai.type' => 1])
                ->andWhere(['aa.item_name' => $this->role])
            ;
            $this->_filterContent .= Yii::t('app', 'Роль') . '"' . $this->roleDict[$this->role] . '";' ;
        }

        if (!empty($this->emails)) {
            $query->andWhere(['LIKE', 'user.emails', $this->emails]);
            $this->_filterContent .= ' Email "' . $this->emails . '";' ;
        }

        if (!empty($this->username)) {
            $query->andWhere(['user.username' => $this->username]);
            $this->_filterContent .= Yii::t('app', 'Логин') . '"' . $this->username . '";' ;
        }


        if (!empty($this->first_name)) {
            $query->andWhere(['like', 'user_data.first_name', $this->first_name]);
            $this->_filterContent .= Yii::t('app', 'Имя') . '"' . $this->first_name . '";' ;
        }

        if (!empty($this->middle_name)) {
            $query->andWhere(['like', 'user_data.middle_name', $this->middle_name]);
            $this->_filterContent .= Yii::t('app', 'Отчество') . '"' . $this->middle_name . '";' ;
        }

        if (!empty($this->last_name)) {
            $query->andWhere(['like', 'user_data.last_name', $this->last_name]);
            $this->_filterContent .= Yii::t('app', 'Фамилия') . '"' . $this->last_name . '";' ;
        }

        if ($this->showStatusActive =='1'){
            $query->andWhere(['user.status' => UserM::STATUS_ACTIVE]);
            $this->_filterContent .= Yii::t('app', 'Только активные') . ';' ;
        }

        if (!empty($this->datetime_min) && !empty($this->datetime_max)) {
            $query->andWhere(['>=','user.created_at', strtotime($this->datetime_min)])
                  ->andWhere(['<=','user.created_at', strtotime($this->datetime_max)]);
            $this->_filterContent .= Yii::t('app', 'Создан') . '"' . $this->datetime_range . '";' ;
        }
//        $e = $query->createCommand()->getSql();

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
}