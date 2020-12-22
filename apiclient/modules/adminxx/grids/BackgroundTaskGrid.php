<?php

namespace app\modules\adminxx\grids;

use app\modules\adminxx\grids\filters\BackgroundTaskFilter;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use common\widgets\xgrid\models\Grid;
use common\widgets\menuAction\MenuActionAsset;
use app\modules\adminxx\grids\filters\UControlFilter;

class BackgroundTaskGrid extends Grid
{
    /**
     * @return array
     */
    public function gridConfig()
    {
        return [
            'name' => 'backgroundTaskGrid',
            'dataProvider' => $this->provider,
            'useAjax' => true,
            'useActions' => true,
            'useCustomUploadFunction' => true,
            'assetsToRegister' => [MenuActionAsset::class],
            'filterView' => '@app/modules/adminxx/grids/views/_filterBackgroundTask',
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
                    'headerOptions' => ['style' => 'width: 3%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 3%; overflow: hidden'],
                ],
                [
                    'attribute' => 'pid',
                    'headerOptions' => ['style' => 'width: 3%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 3%; overflow: hidden'],
                ],
                [
                    'headerOptions' => ['style' => 'width: 5%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 5%; overflow: hidden'],
                    'label' => 'Статус PID',
                    'content'=>function($data){
                        return ($data->isRunning)
                            ? "<span class='blink_text_no_active_waiting_to_active' >Працюе</span>"
                            : "<span>Не працюе</span>";
                    },
                ],
                [
                    'attribute' => 'status',
                    'label' => 'Статус БД',
                    'headerOptions' => ['style' => 'width: 5%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 5%; overflow: hidden'],
                ],
                [
                    'attribute' => 'progress',
                    'label' => 'Прогрес',
                    'headerOptions' => ['style' => 'width: 5%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 5%; overflow: hidden'],
                ],
                [
                    'attribute' => 'model',
                    'headerOptions' => ['style' => 'width: 12%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 12%; overflow: hidden'],
                ],
                [
                    'attribute' => 'result',
                    'headerOptions' => ['style' => 'width: 20%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 20%; overflow: hidden'],
                ],

                [
                    'attribute' => 'datetime_create',
                    'label' => 'Створено',
                    'headerOptions' => ['style' => 'width: 7%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 7%; overflow: hidden'],
                ],
                [
                    'attribute' => 'time_limit',
                    'label' => 'Лимит',
                    'headerOptions' => ['style' => 'width: 3%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 3%; overflow: hidden'],
                ],
                [
                    'label' => 'Время',
                    'headerOptions' => ['style' => 'width: 3%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 3%; overflow: hidden'],
                    'content'=>function($data){
                        return ($data->timeLimitExpired) ? "is over" : "is not over";
                    },
                ],
                [
                    'label' => '',
                    'headerOptions' => ['style' => 'width: 1%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 1%; overflow: hidden'],
                    'content'=>function($data){
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', null,
                            [
                                'title' => 'Переглянути',
                                'onClick' => "modalOpenBackgroundTask($data->id, 'view');"
                            ]);
                    },
                ],
                [
                    'label' => '',
                    'headerOptions' => ['style' => 'width: 1%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 1%; overflow: hidden'],
                    'content'=>function($data){
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', null,
                            [
                                'title' => 'Видалити',
                                'onClick' => "modalOpenBackgroundTask($data->id, 'delete');"
                            ]);
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
            'filterModelClass' => BackgroundTaskFilter::class,
            'conserveName' => 'backgroundTasksGrid',
            'pageSize' => 15,
        ];
    }

}