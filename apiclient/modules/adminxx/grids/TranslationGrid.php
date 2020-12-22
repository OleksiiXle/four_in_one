<?php

namespace app\modules\adminxx\grids;

use Yii;
use yii\helpers\Url;
use common\widgets\xgrid\models\Grid;
use common\widgets\menuAction\MenuActionWidget;
use common\widgets\menuAction\MenuActionAsset;
use app\modules\adminxx\grids\filters\TranslationFilter;

class TranslationGrid extends Grid
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
            'filterView' => '@app/modules/adminxx/grids/views/_filterTranslation',
            //-------------------------------------------
            'columns' => [
                [
                    'label' => '',
                    'headerOptions' => ['style' => 'width: 2%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 2%; white-space: nowrap; overflow: hidden;'],
                    'options' => ['class' => 'row-check'],
                    //'content' => '',
                ],
                [
                    'class' => 'yii\grid\SerialColumn',
                    'headerOptions' => ['style' => 'width: 2%;'],
                    'contentOptions' => ['style' => 'width: 2%;'],
                ],
                [
                    'attribute' => 'id',
                    'headerOptions' => ['style' => 'width: 2%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 2%; overflow: hidden'],
                ],
                [
                    'attribute' => 'tkey',
                    'headerOptions' => ['style' => 'width: 2%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 2%; overflow: hidden'],
                ],
                [
                    'attribute' => 'language',
                    'headerOptions' => ['style' => 'width: 5%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 5%; overflow: hidden'],
                ],
                [
                    'attribute' => 'message',
                    'headerOptions' => ['style' => 'width: 30%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 10%; overflow: hidden'],
                ],
                [
                    'attribute' => 'link1',
                    'headerOptions' => ['style' => 'width: 25%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 10%; overflow: hidden'],
                ],
                [
                    'attribute' => 'link2',
                    'headerOptions' => ['style' => 'width: 25%;overflow: hidden; '],
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
                                    Yii::t('app', 'Изменить') => [
                                        'icon' => 'glyphicon glyphicon-pencil',
                                        'route' => Url::toRoute(['/adminxx/translation/update', 'id' => $data['id']]),
                                    ],
                                    Yii::t('app', 'Удалить') => [
                                        'icon' => 'glyphicon glyphicon-trash',
                                        'route' => Url::toRoute(['/adminxx/translation/delete','tkey' => $data['tkey']]),
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
            'filterModelClass' => TranslationFilter::class,
            'conserveName' => 'translationGrid',
            'pageSize' => 15,
            ];
    }

}