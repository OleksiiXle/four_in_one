<?php

namespace app\modules\adminxx\grids;

use app\modules\adminxx\grids\filters\OauthClientFilter;
use common\widgets\menuAction\MenuActionWidget;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use common\widgets\xgrid\models\Grid;
use common\widgets\menuAction\MenuActionAsset;
use app\modules\adminxx\grids\filters\UControlFilter;

class OauthClientGrid extends Grid
{
    /**
     * @return array
     */
    public function gridConfig()
    {
        return [
            'name' => 'oauthClientGrid',
            'dataProvider' => $this->provider,
            'useAjax' => false,
            'useActions' => false,
            'useCustomUploadFunction' => true,
            'assetsToRegister' => [MenuActionAsset::class],
            'filterView' => '@app/modules/adminxx/grids/views/_filterOauthClient',
            'primaryKey' => 'client_id',
            //-------------------------------------------
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                    'headerOptions' => ['style' => 'width: 3%;'],
                    'contentOptions' => ['style' => 'width: 3%;'],
                ],
                [
                    'attribute' => 'client_id',
                    'headerOptions' => ['style' => 'width: 5%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 5%; overflow: hidden'],
                ],
                [
                    'attribute' => 'redirect_uri',
                    'headerOptions' => ['style' => 'width: 5%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 5%; overflow: hidden'],
                ],
                [
                    'attribute' => 'client_secret',
                    'headerOptions' => ['style' => 'width: 5%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 5%; overflow: hidden'],
                ],
                [
                    'attribute' => 'grant_type',
                    'headerOptions' => ['style' => 'width: 5%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 5%; overflow: hidden'],
                ],
                [
                    'attribute' => 'scope',
                    'headerOptions' => ['style' => 'width: 5%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 5%; overflow: hidden'],
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
                                    Yii::t('app', 'Изменить данные') => [
                                        'icon' => 'glyphicon glyphicon-pencil',
                                        'route' => Url::toRoute(['/adminxx/oauth/update-oauth-client',
                                            'mode' => 'update', 'client_id' => $data['client_id'],]),
                                    ],
                                    Yii::t('app', 'Удалить') => [
                                        'icon' => 'glyphicon glyphicon-trash',
                                        'route' => Url::toRoute(['/adminxx/oauth/delete-oauth-client', 'client_id' => $data['client_id']]),
                                        'confirm' => Yii::t('app', 'Подтвердите удаление'),
                                    ],
                                ],
                                'offset' => -100,
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
            'filterModelClass' => OauthClientFilter::class,
            'conserveName' => 'oauthClientGrid',
            'pageSize' => 10,

        ];
    }

}