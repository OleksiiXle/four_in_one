<?php

namespace app\modules\adminxx\grids;

use Yii;
use yii\helpers\Url;
use app\modules\adminxx\grids\filters\ProviderFilter;
use common\widgets\menuAction\MenuActionWidget;
use common\widgets\xgrid\models\Grid;
use common\widgets\menuAction\MenuActionAsset;

class ProviderGrid extends Grid
{
    /**
     * @return array
     */
    public function gridConfig()
    {
        return [
            'name' => 'providerGrid',
            'dataProvider' => $this->provider,
            'useAjax' => false,
            'useActions' => false,
            'useCustomUploadFunction' => true,
            'assetsToRegister' => [MenuActionAsset::class],
            'filterView' => '@app/modules/adminxx/grids/views/_filterProvider',
            //-------------------------------------------
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                    'headerOptions' => ['style' => 'width: 2%;'],
                    'contentOptions' => ['style' => 'width: 2%;'],
                ],
                [
                    'attribute' => 'class',
                    'headerOptions' => ['style' => 'width: 2%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 2%; overflow: hidden'],
                ],
                [
                    'attribute' => 'name',
                    'headerOptions' => ['style' => 'width: 2%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 2%; overflow: hidden'],
                ],
                [
                    'attribute' => 'client_id',
                    'headerOptions' => ['style' => 'width: 2%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 2%; overflow: hidden'],
                ],
                [
                    'attribute' => 'client_secret',
                    'headerOptions' => ['style' => 'width: 5%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 5%; overflow: hidden'],
                ],
                [
                    'attribute' => 'state_storage_class',
                    'headerOptions' => ['style' => 'width: 30%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 10%; overflow: hidden'],
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
                                'items' => [
                                    'Изменить' => [
                                        'icon' => 'glyphicon glyphicon-pencil',
                                        'route' => Url::toRoute(['/adminxx/provider/update', 'id' => $data['id']]),
                                    ],
                                    'Удалить' => [
                                        'icon' => 'glyphicon glyphicon-trash',
                                        'route' => Url::toRoute(['/adminxx/provider/delete','id' => $data['id']]),
                                        'confirm' => 'Подтвердите удаление',
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
            'filterModelClass' => ProviderFilter::class,
            'conserveName' => 'oauthClientGrid',
            'pageSize' => 10,

        ];
    }

}