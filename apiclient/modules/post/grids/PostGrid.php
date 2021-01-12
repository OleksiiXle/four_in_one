<?php

namespace app\modules\post\grids;

use app\modules\post\grids\filters\PostFilter;
use common\widgets\menuAction\MenuActionWidget;
use common\widgets\xgrid\models\Grid;
use common\widgets\xgrid\models\GridApi;
use Yii;
use yii\helpers\Url;
use common\widgets\menuAction\MenuActionAsset;

class PostGrid extends Grid
{
    /**
     * @return array
     */
    public function gridConfig()
    {
        return [
            'name' => 'apiPostGrid',
            'dataProvider' => $this->provider,
            'useAjax' => true,
            'useActions' => true,
            'useCustomUploadFunction' => false,
            'assetsToRegister' => [MenuActionAsset::class],
            'filterView' => '@app/modules/post/grids/views/_filterPost',
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
                    'attribute' => 'name',
                    'headerOptions' => ['style' => 'width: 5%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 5%; overflow: hidden'],
                ],
                [
                    'attribute' => 'user_id',
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
                                        'route' => Url::toRoute(['/post/post/update',
                                            'mode' => 'update', 'id' => $data['id'],]),
                                    ],
                                    Yii::t('app', 'Удалить') => [
                                        'icon' => 'glyphicon glyphicon-trash',
                                        'route' => Url::toRoute(['/post/post/delete', 'id' => $data['id']]),
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
            'filterModelClass' => PostFilter::class,
           // 'link' => '/post/grid',
            'conserveName' => 'apiPostGrid',
            'pageSize' => 1,
          //  'apiMethod' => 'POST',
            'sort' => [
                'attributes' => [
                    'name' => [
                        'asc' => [
                            'name' => SORT_ASC,
                        ],
                        'desc' => [
                            'name' => SORT_DESC,
                        ],
                    ],
                    'user_id' => [
                        'asc' => [
                            'user_id' => SORT_ASC,
                        ],
                        'desc' => [
                            'user_id' => SORT_DESC,
                        ],
                    ],

                ],
                'enableMultiSort' => true,
            ],
        ];
    }

}