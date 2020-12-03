<?php
use yii\helpers\Html;
use common\widgets\xlegrid\Xlegrid;
use common\widgets\menuAction\MenuActionWidget;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Посты');
?>

<div class="container-fluid">
    <div class="row ">
        <div class="xHeader">
            <div class="col-md-6" align="left">
            </div>
            <div class="col-md-6" align="right" >
                <?php
                echo Html::a('Добавить новый', \yii\helpers\Url::toRoute('/post/post/create'), [
                    'class' =>'btn btn-primary',
                ]);
                ?>
            </div>
        </div>
    </div>
    <div class="row xContent">
        <div class="postGrid xCard">
            <div id="posts-grid-container" >
                <?php
                echo Xlegrid::widget([
                    'usePjax' => true,
                    'pjaxContainerId' => 'posts-grid-container',
                    'useCheckForRows' => true,
                    'checkActionList' => [
                        'actions' => [
                            'deleteChecked' => Yii::t('app', 'Удалить отмеченные'),
                            'action2' => 'action3***',
                            'action3' => 'action3***',
                        ],
                        'options' => [
                            'class' => 'checkActionsSelect',
                            'onchange' => 'actionWithCheckedPosts(this);',
                        ],
                    ],
                    'pager' => [
                        'firstPageLabel' => '<<<',
                        'lastPageLabel'  => '>>>'
                    ],
                    'dataProvider' => $dataProvider,
                    'filterView' => '@app/modules/post/views/post/_filterPost',
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
                            'attribute' => 'name',
                            'headerOptions' => ['style' => 'width: 5%;overflow: hidden; '],
                            'contentOptions' => ['style' => 'width: 5%; overflow: hidden'],
                        ],
                        [
                            'attribute' => 'ownerLastName',
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
                                        'items' => [
                                            Yii::t('app', 'Изменить') => [
                                                'icon' => 'glyphicon glyphicon-pencil',
                                                'route' => Url::toRoute(['/post/post/update', 'id' => $data['id']]),
                                            ],
                                            Yii::t('app', 'Удалить') => [
                                                'icon' => 'glyphicon glyphicon-trash',
                                                'route' => Url::toRoute(['/post/post/delete','id' => $data['id']]),
                                                'confirm' => 'Подтвердите удаление',
                                            ],
                                        ],
                                        'offset' => -100,
                                    ]
                                );
                            },
                        ],
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>
</div>





