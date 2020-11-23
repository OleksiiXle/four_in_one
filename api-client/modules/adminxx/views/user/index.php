<?php
use yii\helpers\Html;
use app\modules\adminxx\models\UserM;
use app\widgets\xlegrid\Xlegrid;
use app\widgets\menuAction\MenuActionWidget;
use yii\helpers\Url;
use app\modules\adminxx\assets\AdminxxUserAsset;

AdminxxUserAsset::register($this);

$this->title = Yii::t('app', 'Пользователи');

?>
<style>
    .usersGrid{
        padding: 5px;
    }
</style>

<div class="row ">
    <div class="xHeader">
        <div class="col-md-6" align="left">
        </div>
        <div class="col-md-6" align="right" >
            <?php
            echo Html::a( 'Рєєстрація нового користувача', '/adminxx/user/signup-by-admin', [
                'class' =>'btn btn-primary',
            ]);
            ?>
        </div>
    </div>
</div>
<div class="row xContent">
        <div class="usersGrid xCard">
            <?php
             //Pjax::begin(['id' => 'users-grid-container1',]);
            ?>
            <div id="users-grid-container" >
                <?php
                echo Xlegrid::widget([
                    'usePjax' => true,
                    'pjaxContainerId' => 'users-grid-container',
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
                    'filterView' => '@app/modules/adminxx/views/user/_filterUser',
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
                            'headerOptions' => ['style' => 'width: 3%;'],
                            'contentOptions' => ['style' => 'width: 3%;'],
                        ],
                        [
                            'attribute' => 'id',
                            'headerOptions' => ['style' => 'width: 3%;overflow: hidden; '],
                            'contentOptions' => ['style' => 'width: 3%; overflow: hidden'],
                        ],
                        [
                            'attribute' => 'username',
                            'headerOptions' => ['style' => 'width: 10%;overflow: hidden; '],
                            'contentOptions' => ['style' => 'width: 10%; overflow: hidden'],
                        ],
                        [
                            'attribute' => 'nameFam',
                            'headerOptions' => ['style' => 'width: 10%;overflow: hidden; '],
                            'contentOptions' => ['style' => 'width: 10%; overflow: hidden'],
                        ],
                        [
                            'attribute' => 'nameNam',
                            'headerOptions' => ['style' => 'width: 7%;overflow: hidden; '],
                            'contentOptions' => ['style' => 'width: 7%; overflow: hidden'],
                        ],
                        [
                            'attribute' => 'nameFat',
                            'headerOptions' => ['style' => 'width: 7%; overflow: hidden;'],
                            'contentOptions' => ['style' => 'width: 7%; overflow: hidden'],
                        ],
                        [
                            'attribute' => 'userRoles',
                            'headerOptions' => ['style' => 'width: 8%;overflow: hidden; '],
                            'contentOptions' => ['style' => 'width: 8%; overflow: hidden;'],
                        ],
                        [
                            'attribute' => 'lastVisitTimeTxt',
                            'label' => 'Час ост. дії',
                            'headerOptions' => ['style' => 'width: 8%;overflow: hidden; '],
                            'contentOptions' => ['style' => 'width: 8%; white-space: nowrap; overflow: hidden;'],
                        ],
                        [
                            'attribute' => 'created_at_str',
                            'headerOptions' => ['style' => 'width: 7%; overflow: hidden;'],
                            'contentOptions' => ['style' => 'width: 7%; overflow: hidden'],
                        ],
                        [
                            'attribute' => 'status',
                            'headerOptions' => ['style' => 'width: 6%;overflow: hidden; '],
                            'contentOptions' => ['style' => 'width: 6%; white-space: nowrap; overflow: hidden;'],
                            'label'=>'Активність',
                            'content'=>function($data){
                                return Html::a('<span class="glyphicon glyphicon-star"></span>', false,
                                    [
                                        'style' => ($data->status == UserM::STATUS_ACTIVE)
                                            ? 'color: red;' : 'color: grey;',
                                        'title' => ($data->status == UserM::STATUS_ACTIVE)
                                            ? 'Активувати' : 'Деактивувати',
                                        'onclick' => 'changeUserActivity("' . $data->id . '");',
                                        'id' => 'activityIcon_' . $data->id,
                                    ]);
                            },
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
                                            'Перегляд інформації' => [
                                                'icon' => 'glyphicon glyphicon-eye-open',
                                                'route' => Url::to(['/adminxx/user/view', 'id' => $data['id']]),
                                            ],
                                            'Змінити данні' => [
                                                'icon' => 'glyphicon glyphicon-pencil',
                                                'route' => Url::to(['/adminxx/user/update-by-admin', 'id' => $data['id']]),
                                            ],
                                            'Змінити дозвіли та ролі' => [
                                                'icon' => 'glyphicon glyphicon-lock',
                                                'route' => Url::to(['/adminxx/user/update-user-assignments', 'id' => $data['id']]),
                                            ],
                                            /*
                                            'Переглянути консерву' => [
                                                'icon' => 'glyphicon glyphicon-lock',
                                                'route' => Url::to(['/adminxx/user/conservation', 'id' => $data['id']]),
                                            ],
                                            */
                                        ],
                                        'offset' => -200,

                                    ]
                                );
                            },
                        ],
                    ],

                ]);
                ?>
                <?php //Pjax::end() ?>

            </div>

        </div>
</div>





