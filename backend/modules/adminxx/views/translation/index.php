<?php
use yii\helpers\Html;
use common\widgets\xlegrid\Xlegrid;
use common\widgets\menuAction\MenuActionWidget;
use yii\helpers\Url;
use backend\modules\adminxx\assets\AdminxxTranslationsAsset;

AdminxxTranslationsAsset::register($this);
$this->title = Yii::t('app', 'Переводы');
?>
<div class="row ">
    <div class="xHeader">
        <div class="col-md-6" align="left">
        </div>
        <div class="col-md-6" align="right" >
            <?php
            echo Html::a('Добавить новый', '/adminxx/translation/create', [
                'class' =>'btn btn-primary',
            ]);
            ?>
        </div>
    </div>
</div>
<div class="row xContent">
    <div class="usersGrid xCard">
        <div id="translations-grid-container" >
            <?php
            echo Xlegrid::widget([
                'usePjax' => true,
                'pjaxContainerId' => 'translations-grid-container',
                'useCheckForRows' => true,
                'checkActionList' => [
                    'actions' => [
                        'deleteChecked' => Yii::t('app', 'Удалить отмеченные'),
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
                'filterView' => '@app/modules/adminxx/views/translation/_filterTranslation',
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
                                        'Изменить' => [
                                            'icon' => 'glyphicon glyphicon-pencil',
                                            'route' => Url::to(['/adminxx/translation/update', 'id' => $data['id']]),
                                        ],
                                        'Удалить' => [
                                            'icon' => 'glyphicon glyphicon-trash',
                                            'route' => Url::to(['/adminxx/translation/delete','tkey' => $data['tkey']]),
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
