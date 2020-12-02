<?php

use yii\helpers\Html;
use yii\bootstrap\Modal;
use common\widgets\xlegrid\Xlegrid;
use apiadmin\modules\adminxx\assets\AdminxxBackgroundTaskAsset;

AdminxxBackgroundTaskAsset::register($this);

$this->title =  'Фонові завдання';
?>

<div class="row ">
    <div class="xHeader">
    </div>
</div>
<div class="row xContent">
        <div class="tasksGrid xCard">
            <div id="background-tasks-grid" >
                <?php
                echo Xlegrid::widget([
                    'useCheckForRows' => true,
                    'checkActionList' => [
                        'actions' => [
                            'action1' => 'action1***',
                            'action2' => 'action2***',
                            'action3' => 'action3***',
                        ],
                        'options' => [
                            'class' => 'checkActionsSelect',
                            'onchange' => 'actionWithChecked(this);',
                        ],
                    ],
                    'pager' => [
                        'firstPageLabel' => '<<<',
                        'lastPageLabel'  => '>>>'
                    ],
                    'dataProvider' => $dataProvider,
                    'gridTitle' => '',
                    'additionalTitle' => 'qq',
                    'usePjax' => true,
                    'pjaxContainerId' => 'background-tasks-grid',
                    'filterView' => '@app/modules/adminxx/views/background-tasks/_filterBackgroundTask',
                    //-------------------------------------------
                    'tableOptions' => [
                        'class' => 'table table-bordered table-hover table-condensed',
                        'style' => ' width: 100%; table-layout: fixed;',
                    ],
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
                            'attribute' => 'id',
                            'headerOptions' => ['style' => 'width: 3%;overflow: hidden; '],
                            'contentOptions' => ['style' => 'width: 3%; overflow: hidden'],
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
                        /*
                        [
                            'label' => '',
                            'headerOptions' => ['style' => 'width: 1%;overflow: hidden; '],
                            'contentOptions' => ['style' => 'width: 1%; overflow: hidden'],
                            'content'=>function($data){
                                return Html::a('<span class="glyphicon glyphicon-eye-open"></span>',
                                    Url::to(['/adminxx/background-tasks/view', 'id' => $data->id]),
                                    [
                                        'title' => 'Переглянути',
                                    ]);
                            },
                        ],
                        */
                        [
                            'label' => '',
                            'headerOptions' => ['style' => 'width: 1%;overflow: hidden; '],
                            'contentOptions' => ['style' => 'width: 1%; overflow: hidden'],
                            'content'=>function($data){
                                return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', null,
                                    [
                                        'title' => 'Переглянути',
                                        'class' => 'no-pjax',
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
                                        'class' => 'no-pjax',
                                        'onClick' => "modalOpenBackgroundTask($data->id, 'delete');"
                                    ]);
                            },
                        ],

                        //     'model',
                          //  'arguments',
                         //   'result_file',
                      //  'datetime_update',


                        //  'result',
                            /*
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
                            'attribute' => 'model',
                            'headerOptions' => ['style' => 'width: 20%;overflow: hidden; '],
                            'contentOptions' => ['style' => 'width: 20%; overflow: hidden'],
                        ],
                            */
                        /*
                        [
                            'attribute'=>'spec_document',
                            'headerOptions' => ['style' => 'width: 4%;overflow: hidden;'],
                            'contentOptions' => ['style' => 'width: 4%; overflow: hidden;'],
                            'label'=>'Жетон',
                            'content'=>function($data){
                                return (isset($data->userDatas->spec_document)) ? $data->userDatas->spec_document : '';
                            },
                        ],
                        */
                    ],

                ]);
                ?>
            </div>
        </div>
</div>

<?php //***********************************  заготовки под модальные окна

//---- среднее окно
Modal::begin([
    'headerOptions' => ['id' => 'modalHeader_lg','class'=>'text-center'],
    'id' => 'main-modal-lg',
    'size' => 'modal-lg',
    //  'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
]);?>
<div style="overflow: auto;" id='modalContent_lg'></div><br>
<?php yii\bootstrap\Modal::end();?>








