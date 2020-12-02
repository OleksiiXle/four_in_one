<?php
namespace apiadmin\modules\adminxx\controllers;

use Yii;
use common\components\AccessControl;
use apiadmin\controllers\MainController;

class DefaultController extends MainController
{
   /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'index',
                    ],
                    'roles'      => ['menuAdminxMain', ],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        $userPermissions = Yii::$app->authManager->userRolesPermissions;
        $buttons = [
            'users' => (isset($userPermissions['adminUsersView']))
                ? [
                    'show' => true,
                    'name' => Yii::t('app', 'Пользователи'),
                    'route' => '/adminxx/user'
                ]
                : [
                    'show' => false,
                ],
            'rules' => (isset($userPermissions['adminAuthItemList']))
                ? [
                    'show' => true,
                    'name' => Yii::t('app', 'Правила'),
                    'route' => '/adminxx/rule'
                ]
                : [
                    'show' => false,
                ],
            'authItems' => (isset($userPermissions['adminAuthItemList']))
                ? [
                    'show' => true,
                    'name' => Yii::t('app', 'Разрешения'),
                    'route' => '/adminxx/auth-item'
                ]
                : [
                    'show' => false,
                ],
            'menuEdit' => (isset($userPermissions['adminMenuEdit']))
                ? [
                    'show' => true,
                    'name' => Yii::t('app', 'Редактор меню'),
                    'route' => '/adminxx/menux/menu'
                ]
                : [
                    'show' => false,
                ],
            'configs' => (isset($userPermissions['adminConfigUpdate']))
                ? [
                    'show' => true,
                    'name' => Yii::t('app', 'Системные настройки'),
                    'route' => '/adminxx/configs/update'
                ]
                : [
                    'show' => false,
                ],
            'guestControl' => (isset($userPermissions['adminGuestControl']))
                ? [
                    'show' => true,
                    'name' => Yii::t('app', 'Посещения сайта'),
                    'route' => '/adminxx/check/guest-control'
                ]
                : [
                    'show' => false,
                ],
            'Translations' => (isset($userPermissions['menuAdminxMain']))
                ? [
                    'show' => true,
                    'name' => Yii::t('app', 'Переводы'),
                    'route' => 'adminxx/translation/index'
                ]
                : [
                    'show' => false,
                ],

            'PHPinfo' => (isset($userPermissions['menuAdminxMain']))
                ? [
                    'show' => true,
                    'name' => 'PHP-info',
                    'route' => 'adminxx/user/php-info'
                ]
                : [
                    'show' => false,
                ],

        ];
        $r=1;
        return $this->render('index',
            [
                'buttons' => $buttons,
            ]);
    }
}
