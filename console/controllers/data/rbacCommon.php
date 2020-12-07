<?php
return [
    'permissions' => [

        //***************************************************************** Общие роуты
        'menuAll'                          => 'Загальні пункти меню',


        //***************************************************************** Администрирование
        'menuAdminxMain'          => 'Системне адміністрування (меню)',

        'menuAdminUsersView'          => '*** Просмотр списка пользователей (меню)',
        'adminUsersView'              => 'Просмотр списка пользователей',
        'adminUserView'               => 'Просмотр профиля пользователя',
        'adminUserCreate'             => 'Создание пользователя администратором',
        'adminUserUpdate'             => 'Стандартное редактирование пользователя администратором ',
        'adminChangeUserAssignments'  => 'Нестандартное редактирование прав доступа пользователя',
        'adminChangeUserActivity'     => 'Изменение активности пользователя',

        'menuAdminGuestControl'       => '*** Контроль посещения сайта (меню)',
        'adminGuestControl'           => 'Контроль посещения сайта',
        'adminGuestControlDelete'     => 'Очистка БД контроля посещений',

        'menuAdminAuthItemList'       => '*** Просмотр списка ролей, разрешений (меню)',
        'adminAuthItemList'           => 'Просмотр списка ролей, разрешений',
        'adminAuthItemCRUD'           => 'Создание, удаление правила, разрешения',

        'menuAdminMenuEdit'           => '*** Редактор меню (меню)',
        'adminMenuEdit'               => 'Редактор меню',

        'menuAdminConfigUpdate'       => '*** Изменение системных настроек (меню)',
        'adminConfigUpdate'           => 'Изменение системных настроек',

        'menuAdminTranslateUpdate'    => '*** Изменение словарей (меню)',
        'adminTranslateUpdate'        => '*** Изменение словарей',
        //***************************************************************** Посты
        'postCRUD'                    => 'Изменение постов',


    ],
    'roles' => [
        'adminSuper' => 'Демиург',
        'adminSystem' => 'Системный администратор (Технический)',
        'adminUsers' => 'Системный администратор (По персоналу)',
        'adminUsersAdvanced' => 'Системный администратор (По персоналу, продвинутый - нестандартые роли и разрешения)',

        'user'        => 'Зарегистрированный пользователь',
    ],
    'rolesPermissions' => [
        'adminSuper' => [
            'menuAdminxMain',
            'menuAdminUsersView',
            'adminUsersView',
            'adminUserView',
            'adminUserCreate',
            'adminUserUpdate',
            'adminChangeUserAssignments',
            'adminChangeUserActivity',
            'menuAdminGuestControl',
            'adminGuestControl',
            'adminGuestControlDelete',
            'menuAdminAuthItemList',
            'adminAuthItemList',
            'adminAuthItemCRUD',
            'menuAdminMenuEdit',
            'adminMenuEdit',
            'menuAdminConfigUpdate',
            'adminConfigUpdate',
            'menuAdminTranslateUpdate',
            'adminTranslateUpdate',
            'postCRUD',
        ],
        'adminSystem' => [
            'menuAdminxMain',

            'menuAdminTranslateUpdate',
            'menuAdminUsersView',
            'menuAdminAuthItemList',
            'menuAdminMenuEdit',
            'menuAdminConfigUpdate',
            'menuAdminGuestControl',

            'adminUsersView',
            'adminUserView',
            'adminGuestControl',
            'adminGuestControlDelete',
            'adminAuthItemList',
            'adminAuthItemCRUD',
            'adminMenuEdit',
            'adminConfigUpdate',
            'adminTranslateUpdate',
        ],
        'adminUsers' => [
            'menuAdminxMain',
            'menuAdminUsersView',
            'menuAdminAuthItemList',
            'menuAdminGuestControl',

            'adminUsersView',
            'adminUserView',
            'adminUserCreate',
            'adminUserUpdate',
            'adminChangeUserActivity',
            'adminGuestControl',
            'adminAuthItemList',
        ],
        'adminUsersAdvanced' => [
            'adminChangeUserAssignments',
        ],
        'user' => [
            'menuAll',
            'postCRUD',
        ],
    ],
    'rolesChildren' => [
        'adminSuper' => [
            'adminSystem',
            'adminUsers',
            'adminUsersAdvanced'
        ],
        'adminSystem' => [
            'user',
        ],
        'adminUsers' => [
            'user',
        ],
        'adminUsersAdvanced' => [
            'adminUsers',
        ],
    ]
];