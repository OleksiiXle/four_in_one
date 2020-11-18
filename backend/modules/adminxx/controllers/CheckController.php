<?php

namespace backend\modules\adminxx\controllers;

use backend\controllers\MainController;
use common\components\conservation\ActiveDataProviderConserve;
use common\components\AccessControl;
use backend\modules\adminxx\models\filters\UControlFilter;
use backend\modules\adminxx\models\filters\UserActivityFilter;
use backend\modules\adminxx\models\UControl;
use backend\modules\adminxx\models\UserData;
use backend\modules\adminxx\models\UserM;

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
        $dataProvider = new ActiveDataProviderConserve([
            'filterModelClass' => UControlFilter::class,
            'conserveName' => 'guestActivityGrid',
            'pageSize' => 15,
            'sort' => ['attributes' => [
                'user_id' => [
                    'asc' => [
                        'uc.user_id' => SORT_ASC,
                    ],
                    'desc' => [
                        'uc.user_id' => SORT_DESC,
                    ],
                ],
                'remote_ip' => [
                    'asc' => [
                        'uc.remote_ip' => SORT_ASC,
                    ],
                    'desc' => [
                        'uc.remote_ip' => SORT_DESC,
                    ],
                ],
                'username' => [
                    'asc' => [
                        'user.username' => SORT_ASC,
                    ],
                    'desc' => [
                        'user.username' => SORT_DESC,
                    ],
                ],
                'createdAt' => [
                    'asc' => [
                        'uc.created_at' => SORT_ASC,
                    ],
                    'desc' => [
                        'uc.created_at' => SORT_DESC,
                    ],
                ],
                'updatedAt' => [
                    'asc' => [
                        'uc.updated_at' => SORT_ASC,
                    ],
                    'desc' => [
                        'uc.updated_at' => SORT_DESC,
                    ],
                ],
                'url' => [
                    'asc' => [
                        'uc.url' => SORT_ASC,
                    ],
                    'desc' => [
                        'uc.url' => SORT_DESC,
                    ],
                ],
            ]],

        ]);
        if (\Yii::$app->request->isPost){
            return $this->redirect('guest-control');
        }

        return $this->render('guestsGrid',[
            'dataProvider' => $dataProvider,
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
        return $this->redirect('/adminxx/check/guest-control');
    }

    /**
     * +++ Просмотр профиля зарегистрированного пользователя view-user
     * @return string
     */
    public function actionViewUser($id, $timeFix=0)
    {
        $user_id = $id;
        if (1==1){
            $user = UserM::findOne($user_id);
            $userData = UserData::findOne(['user_id' => $user_id]);
            //-- подразделения
            $depCreateIds = $userData->getChangedItems('Department', 'created', true, $timeFix);
            $depUpdateIds = $userData->getChangedItems('Department', 'updated', true, $timeFix);
            $depDeleteIds = $userData->getChangedItems('Department', 'deleted', true, $timeFix);
            $depIds = $depCreateIds + $depUpdateIds + $depDeleteIds;
            $buf = DepartmentCommon::find()
                ->where(['in', 'id', $depIds ])
                ->all();
            $departments = [];
            $cnt = 1;
            foreach ($buf as $dep){
                $departments[]=[
                    'cnt' => $cnt++,
                    'id' => $dep->id,
                    'operation' => $depIds[$dep->id]['operation'],
                    'name' => $dep->gunpName,
                ];
            }
            //-- должности
            $depCreateIds = $userData->getChangedItems('Position', 'created', true, $timeFix);
            $depUpdateIds = $userData->getChangedItems('Position', 'updated', true, $timeFix);
            $depDeleteIds = $userData->getChangedItems('Position', 'deleted', true, $timeFix);
            $depIds = $depCreateIds + $depUpdateIds + $depDeleteIds;
            $buf = PositionCommon::find()
                ->where(['in', 'id', $depIds ])
                ->all();
            $positions = [];
            $cnt = 1;
            foreach ($buf as $dep){
                $positions[]=[
                    'cnt' => $cnt++,
                    'id' => $dep->id,
                    'operation' => $depIds[$dep->id]['operation'],
                    'name' => $dep->name . ' ' . $dep->department->gunpName,
                ];
            }

            //-- персонал
            $depCreateIds = $userData->getChangedItems('Personal', 'created', true, $timeFix);
            $depUpdateIds = $userData->getChangedItems('Personal', 'updated', true, $timeFix);
            $depDeleteIds = $userData->getChangedItems('Personal', 'deleted', true, $timeFix);
            $depIds = $depCreateIds + $depUpdateIds + $depDeleteIds;
            $buf = PersonalCommon::find()
                ->where(['in', 'id', $depIds ])
                ->all();
            $personal = [];
            $cnt = 1;
            foreach ($buf as $dep){
                $personal[]=[
                    'cnt' => $cnt++,
                    'id' => $dep->id,
                    'operation' => $depIds[$dep->id]['operation'],
                    'name' => '<b>' . $dep->name_family . '</b> ' . $dep->positionCommon->department->gunpName,
                ];
            }

        }
        $user = UserM::findOne($id);
        $uControl = UControl::findOne(['user_id' => $id]);
        $userProfile = $user->userProfile;
        return $this->render('viewUser', [
            'userProfile' => $userProfile,
            'uControl' => $uControl,

            'departments'=> $departments,
            'positions' => $positions,
            'personal' => $personal,
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