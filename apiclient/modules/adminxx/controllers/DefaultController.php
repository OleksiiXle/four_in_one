<?php
namespace app\modules\adminxx\controllers;

use Yii;
use common\components\AccessControl;
use app\controllers\MainController;
use yii\helpers\Url;

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
                    'route' => Url::toRoute('/adminxx/user'),
                ]
                : [
                    'show' => false,
                ],
            'rules' => (isset($userPermissions['adminAuthItemList']))
                ? [
                    'show' => true,
                    'name' => Yii::t('app', 'Правила'),
                    'route' => Url::toRoute('/adminxx/rule')
                ]
                : [
                    'show' => false,
                ],
            'authItems' => (isset($userPermissions['adminAuthItemList']))
                ? [
                    'show' => true,
                    'name' => Yii::t('app', 'Разрешения'),
                    'route' => Url::toRoute('/adminxx/auth-item')
                ]
                : [
                    'show' => false,
                ],
            'menuEdit' => (isset($userPermissions['adminMenuEdit']))
                ? [
                    'show' => true,
                    'name' => Yii::t('app', 'Редактор меню'),
                    'route' => Url::toRoute('/adminxx/menux/menu')
                ]
                : [
                    'show' => false,
                ],
            'configs' => (isset($userPermissions['adminConfigUpdate']))
                ? [
                    'show' => true,
                    'name' => Yii::t('app', 'Системные настройки'),
                    'route' => Url::toRoute('/adminxx/configs/update')
                ]
                : [
                    'show' => false,
                ],
            'guestControl' => (isset($userPermissions['adminGuestControl']))
                ? [
                    'show' => true,
                    'name' => Yii::t('app', 'Посещения сайта'),
                    'route' => Url::toRoute('/adminxx/check/guest-control')
                ]
                : [
                    'show' => false,
                ],
            'Translations' => (isset($userPermissions['menuAdminxMain']))
                ? [
                    'show' => true,
                    'name' => Yii::t('app', 'Переводы'),
                    'route' => Url::toRoute('/adminxx/translation/index')
                ]
                : [
                    'show' => false,
                ],

            'PHPinfo' => (isset($userPermissions['menuAdminxMain']))
                ? [
                    'show' => true,
                    'name' => 'PHP-info',
                    'route' => Url::toRoute('/adminxx/user/php-info')
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
