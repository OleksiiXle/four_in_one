<?php

namespace apiadmin\modules\adminxx\controllers;

use apiadmin\modules\adminxx\grids\CheckGrid;
use Yii;
use apiadmin\modules\adminxx\grids\UsersGrid;
use yii\helpers\Url;
use common\components\conservation\ActiveDataProviderConserve;
use common\components\AccessControl;
use apiadmin\controllers\MainController;
use apiadmin\modules\adminxx\models\filters\UControlFilter;
use apiadmin\modules\adminxx\models\filters\UserActivityFilter;
use apiadmin\modules\adminxx\models\UControl;
use apiadmin\modules\adminxx\models\UserData;
use apiadmin\modules\adminxx\models\UserM;
use yii\web\Response;

/**
 * Class CheckController
 * Прпосмотр активности пользователей (зарегистрированных и гостей)
 * @package app\modules\adminxx\controllers
 */
class CheckController extends MainController
{
    /**
     * @return array
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
                        'guest-control', 'view-user', 'view-guest'
                    ],
                    'roles'      => ['adminGuestControl' ],
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'delete-visitors',
                    ],
                    'roles'      => ['adminGuestControlDelete' ],
                ],
            ],
                /*
            'denyCallback' => function ($rule, $action) {
                \yii::$app->getSession()->addFlash("warning",\Yii::t('app', "Действие запрещено"));
                return $this->redirect(\Yii::$app->request->referrer);

        }
        */
        ];
        return $behaviors;
    }

    /**
     * +++ Список посетителей guest-control
     * @return string
     */
    public function actionGuestControl()
    {
        $checkGrid = new CheckGrid();
        if (Yii::$app->request->isPost) {
            Yii::$app->getResponse()->format = Response::FORMAT_HTML;
            return $checkGrid->reload(Yii::$app->request->post());
        }
        return $this->render('guestsGrid', [
            'checkGrid' => $checkGrid,
        ]);
    }

    /**
     * +++ Очистка БД контроля посещений delete-visitors
     *
     * @return \yii\web\Response
     */
    public function actionDeleteVisitors()
    {
        if (\Yii::$app->request->isPost){
            $mode = \Yii::$app->request->get('mode');
            switch ($mode){
                case 'deleteAll':
                    $ret = UControl::deleteAll();
                    break;
                case 'deleteAllGuests':
                    $ret = UControl::deleteAll(['user_id' => 0]);
                    break;
                case 'deleteOldGuests':
                    $ret = UControl::clearOldRecords();
                    break;
            }
        }
        return $this->redirect(Url::toRoute('/adminxx/check/guest-control'));
    }

    /**
     * +++ Просмотр профиля зарегистрированного пользователя view-user
     * @return string
     */
    public function actionViewUser($id, $timeFix=0)
    {
        $user_id = $id;
        $user = UserM::findOne($id);
        $uControl = UControl::findOne(['user_id' => $id]);
        $userProfile = $user->userProfile;
        return $this->render('viewUser', [
            'userProfile' => $userProfile,
            'uControl' => $uControl,
            'user_id' => $user_id,
            'timeFix' => $timeFix,
        ]);
    }

    /**
     * +++ Просмотр данных гостя view-guest
     * @return string
     */
    public function actionViewGuest($ip)
    {
        $guest = UControl::findOne(['remote_ip' => $ip]);
        return $this->render('viewGuest', [
            'guest' => $guest,
        ]);
    }


    /**
     * @deprecated
     * @return string
     */
    public function actionUserControl()
    {
        $dataProvider = new ActiveDataProviderConserve([
            'filterModelClass' => UserActivityFilter::class,
            'conserveName' => 'userActivityGrid',
            'pageSize' => 20,
            'sort' => ['attributes' => [
                'user_id' => [
                    'asc' => [
                        'u_control.user_id' => SORT_ASC,
                    ],
                    'desc' => [
                        'u_control.user_id' => SORT_DESC,
                    ],
                ],
                'remote_ip' => [
                    'asc' => [
                        'u_control.remote_ip' => SORT_ASC,
                    ],
                    'desc' => [
                        'u_control.remote_ip' => SORT_DESC,
                    ],
                ],
                'username' => [
                    'asc' => [
                        'u_control.username' => SORT_ASC,
                    ],
                    'desc' => [
                        'u_control.username' => SORT_DESC,
                    ],
                ],
                'createdAt' => [
                    'asc' => [
                        'u_control.created_at' => SORT_ASC,
                    ],
                    'desc' => [
                        'u_control.created_at' => SORT_DESC,
                    ],
                ],
                'updatedAt' => [
                    'asc' => [
                        'u_control.updated_at' => SORT_ASC,
                    ],
                    'desc' => [
                        'u_control.updated_at' => SORT_DESC,
                    ],
                ],
                'url' => [
                    'asc' => [
                        'u_control.url' => SORT_ASC,
                    ],
                    'desc' => [
                        'u_control.url' => SORT_DESC,
                    ],
                ],
            ]],

        ]);

        return $this->render('usersGrid',[
            'dataProvider' => $dataProvider,
        ]);
    }


}