<?php

namespace apiadmin\modules\adminxx\grids;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use common\widgets\xgrid\models\Grid;
use common\widgets\menuAction\MenuActionAsset;
use apiadmin\modules\adminxx\grids\filters\UControlFilter;

class CheckGrid extends Grid
{
    /**
     * @return array
     */
    public function gridConfig()
    {
        return [
            'name' => 'checkUsersGrid',
            'dataProvider' => $this->provider,
            'useAjax' => true,
            'useActions' => true,
            'useCustomUploadFunction' => true,
            'assetsToRegister' => [MenuActionAsset::class],
            'filterView' => '@app/modules/adminxx/grids/views/_filterUControl',
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
                    'attribute' => 'user_id',
                    'headerOptions' => ['style' => 'width: 5%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 5%; overflow: hidden'],
                ],
                [
                    'attribute' => 'remote_ip',
                    'headerOptions' => ['style' => 'width: 5%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 5%; overflow: hidden'],
                    'content'=>function($data){
                        $ret = $data->remote_ip;
                        if (empty($data->username)){
                            $ret = Html::a($ret, Url::toRoute(['/adminxx/check/view-guest', 'ip' => $data->remote_ip ]));
                        }
                        return $ret;
                    },

                ],
                [
                    'attribute' => 'username',
                    'headerOptions' => ['style' => 'width: 7%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 7%; overflow: hidden'],
                    'content'=>function($data){
                        $ret = $data->username;
                        if (!empty($data->username)){
                            $ret = Html::a($ret, Url::toRoute(['/adminxx/check/view-user', 'id' => $data->user_id ]));
                        }
                        return $ret;
                    },

                ],
                [
                    'label' => Yii::t('app', 'Пользователь'),
                    'headerOptions' => ['style' => 'width: 10%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 10%; overflow: hidden'],
                    'content'=>function($data){
                        return (isset($data->userDatas)) ? $data->userDatas->last_name: '';
                    },

                ],
                [
                    'attribute' => 'createdAt',
                    'headerOptions' => ['style' => 'width: 10%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 10%; overflow: hidden'],
                ],
                [
                    'attribute' => 'updatedAt',
                    'headerOptions' => ['style' => 'width: 10%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 10%; overflow: hidden'],
                ],
                [
                    'attribute' => 'url',
                    'headerOptions' => ['style' => 'width: 15%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 15%; overflow: hidden'],
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
            'filterModelClass' => UControlFilter::class,
            'conserveName' => 'checkUsersGrid',
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

        ];
    }

}