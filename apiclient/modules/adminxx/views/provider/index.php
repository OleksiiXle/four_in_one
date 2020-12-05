<?php
use yii\helpers\Html;
use app\widgets\xlegrid\Xlegrid;
use app\widgets\menuAction\MenuActionWidget;
use yii\helpers\Url;
use app\modules\adminxx\assets\AdminxxTranslationsAsset;

$this->title = Yii::t('app', 'АПИ клиенты');
?>
<div class="row ">
    <div class="xHeader">
        <div class="col-md-6" align="left">
        </div>
        <div class="col-md-6" align="right" >
            <?php
            echo Html::a('Добавить новый', \yii\helpers\Url::toRoute('/adminxx/provider/create'), [
                'class' =>'btn btn-primary',
            ]);
            ?>
        </div>
    </div>
</div>
<div class="row xContent">
    <div class="providerGrid xCard">
        <div id="providers-grid-container" >
            <?php
            echo Xlegrid::widget([
                'usePjax' => true,
                'pjaxContainerId' => 'providers-grid-container',
              //  'useCheckForRows' => true,
                'checkActionList' => [
                    'actions' => [
                        'action2' => 'action3***',
                        'action3' => 'action3***',
                    ],
                    'options' => [
                        'class' => 'checkActionsSelect',
                        'onchange' => 'actionWithCheckedTranslations(this);',
                    ],
                ],
                'pager' => [
                    'firstPageLabel' => '<<<',
                    'lastPageLabel'  => '>>>'
                ],
                'dataProvider' => $dataProvider,
                'filterView' => '@app/modules/adminxx/views/provider/_filterProvider',
                //-------------------------------------------
                'tableOptions' => [
                    'class' => 'table table-bordered table-hover table-condensed',
                    'style' => ' width: 100%; table-layout: fixed;',
                ],
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
                                            'route' => Url::toRoute(['/adminxx/provider/delete','tkey' => $data['id']]),
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
