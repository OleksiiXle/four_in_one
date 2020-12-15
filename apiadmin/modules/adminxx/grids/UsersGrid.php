<?php

namespace apiadmin\modules\adminxx\grids;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use common\widgets\xgrid\models\Grid;
use common\models\UserM;
use common\widgets\menuAction\MenuActionWidget;
use common\widgets\menuAction\MenuActionAsset;
use apiadmin\modules\adminxx\grids\filters\UserFilter;

class UsersGrid extends Grid
{
    /**
     * @return array
     */
    public function gridConfig()
    {
        return [
            'name' => 'usersGrid',
            'dataProvider' => $this->provider,
            'useAjax' => true,
            'useActions' => true,
            'useCustomUploadFunction' => false,
            'assetsToRegister' => [MenuActionAsset::class],
            'filterView' => '@app/modules/adminxx/grids/views/_filterUser',
            //-------------------------------------------
            'columns' => [
                [
                    'label' => '',
                    'headerOptions' => ['style' => 'width: 2%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 2%; white-space: nowrap; overflow: hidden;'],
                    'options' => ['class' => 'row-check'],
                ],
                [
                    'class' => 'yii\grid\SerialColumn',
                    'headerOptions' => ['style' => 'width: 3%;'],
                    'contentOptions' => ['style' => 'width: 3%;'],
                ],
                [
                    'attribute' => 'id',
                    'headerOptions' => ['style' => 'width: 3%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 3%; overflow: hidden'],
                ],
                [
                    'attribute' => 'username',
                    'headerOptions' => ['style' => 'width: 10%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 10%; overflow: hidden'],
                ],
                [
                    'attribute' => 'last_name',
                    'headerOptions' => ['style' => 'width: 10%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 10%; overflow: hidden'],
                ],
                [
                    'attribute' => 'first_name',
                    'headerOptions' => ['style' => 'width: 7%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 7%; overflow: hidden'],
                ],
                [
                    'attribute' => 'middle_name',
                    'headerOptions' => ['style' => 'width: 7%; overflow: hidden;'],
                    'contentOptions' => ['style' => 'width: 7%; overflow: hidden'],
                ],
                [
                    'attribute' => 'userRoles',
                    'headerOptions' => ['style' => 'width: 8%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 8%; overflow: hidden;'],
                ],
                [
                    'attribute' => 'lastVisitTimeTxt',
                    'headerOptions' => ['style' => 'width: 8%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 8%; white-space: nowrap; overflow: hidden;'],
                ],
                [
                    'attribute' => 'created_at_str',
                    'headerOptions' => ['style' => 'width: 7%; overflow: hidden;'],
                    'contentOptions' => ['style' => 'width: 7%; overflow: hidden'],
                ],
                [
                    'attribute' => 'status',
                    'headerOptions' => ['style' => 'width: 6%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 6%; white-space: nowrap; overflow: hidden;'],
                    'content'=>function($data){
                        return Html::a('<span class="glyphicon glyphicon-star"></span>', false,
                            [
                                'style' => ($data->status == UserM::STATUS_ACTIVE)
                                    ? 'color: red;' : 'color: grey;',
                                'title' => ($data->status == UserM::STATUS_ACTIVE)
                                    ? 'Активувати' : 'Деактивувати',
                                'onclick' => 'changeUserActivity("' . $data->id . '");',
                                'id' => 'activityIcon_' . $data->id,
                            ]);
                    },
                ],
                [
                    'headerOptions' => ['style' => 'width: 3%; '],
                    'contentOptions' => [
                        'style' => 'width: 3%; ',
                    ],
                    'label'=>'',
                    'content'=>function($data){
                        return MenuActionWidget::widget(
                            [
                                'assetsRegister' => false,
                                'items' => [
                                    Yii::t('app', 'Информация') => [
                                        'icon' => 'glyphicon glyphicon-eye-open',
                                        'route' => Url::toRoute(['/adminxx/user/view', 'id' => $data['id']]),
                                    ],
                                    Yii::t('app', 'Изменить данные') => [
                                        'icon' => 'glyphicon glyphicon-pencil',
                                        'route' => Url::toRoute(['/adminxx/user/update-by-admin',
                                            'mode' => 'update', 'id' => $data['id'],]),
                                    ],
                                    Yii::t('app', 'Изменить разрешения и роли') => [
                                        'icon' => 'glyphicon glyphicon-lock',
                                        'route' => Url::toRoute(['/adminxx/user/update-user-assignments', 'id' => $data['id']]),
                                    ],
                                    Yii::t('app', 'Консерва') => [
                                        'icon' => 'glyphicon glyphicon-lock',
                                        'route' => Url::toRoute(['/adminxx/user/conservation', 'user_id' => $data['id']]),
                                    ],
                                ],
                                'offset' => -200,
                            ]
                        );
                    },
                ],
            ],
        ];
    }
    /**
     * @return array
     */
    public function providerConfig()
    {
        return [
            'filterModelClass' => UserFilter::class,
            'conserveName' => 'userAdminGrid',
            'pageSize' => 5,
            'sort' => ['attributes' => [
                'id',
                'username',
                'nameFam' => [
                    'asc' => [
                        'user_data.last_name' => SORT_ASC,
                    ],
                    'desc' => [
                        'user_data.last_name' => SORT_DESC,
                    ],
                ],
                'lastRoutTime' => [
                    'asc' => [
                        'user_data.last_rout_time' => SORT_ASC,
                    ],
                    'desc' => [
                        'user_data.last_rout_time' => SORT_DESC,
                    ],
                ],
                'lastRout' => [
                    'asc' => [
                        'user_data.last_rout' => SORT_ASC,
                    ],
                    'desc' => [
                        'user_data.last_rout' => SORT_DESC,
                    ],
                ],
                'status' => [
                    'asc' => [
                        'user.status' => SORT_ASC,
                    ],
                    'desc' => [
                        'user.status' => SORT_DESC,
                    ],
                ],
            ]],
            ];
    }

}