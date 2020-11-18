<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use backend\components\widgets\xlegrid\Xlegrid;
use yii\helpers\Url;

\backend\modules\adminxx\assets\AdminxxGuestsControlAsset::register($this);

$this->title = \Yii::t('app', 'Відвідувачі');
$interval = (empty($dataProvider->filterModel->getAttributes()['activityInterval']))
    ? 3600
    : $dataProvider->filterModel->getAttributes()['activityInterval'];
$timeFix = time() - $interval;

?>
<div class="container-fluid">

    <div class="row xContent">
        <div class="xCard" style="min-height: 70vh">
            <?php Pjax::begin(['id' => 'gridGuest']);
            echo Xlegrid::widget([
                'dataProvider' => $dataProvider,
                'gridTitle' => '',
                'additionalTitle' => 'qq',
                'filterView' => '@app/modules/adminxx/views/check/_filterUControl',
                //-------------------------------------------
                'tableOptions' => [
                    'class' => 'table table-bordered table-hover table-condensed ',
                    'style' => ' width: 100%; ',
                ],
                //-------------------------------------------
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'headerOptions' => ['style' => 'width: 3%;'],
                        'contentOptions' => ['style' => 'width: 3%;'],
                    ],

                    [
                        'attribute' => 'user_id',
                        'label' => 'UID',
                        'headerOptions' => ['style' => 'width: 3%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 3%; overflow: hidden'],
                    ],
                    [
                        'label' => 'IP',
                        'attribute' => 'remote_ip',
                        'headerOptions' => ['style' => 'width: 5%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 5%; overflow: hidden'],
                        'content'=>function($data){
                            $ret = $data->remote_ip;
                            if (empty($data->username)){
                                $ret = Html::a($ret, Url::to(['/adminxx/check/view-guest', 'ip' => $data->remote_ip ]));
                            }
                            return $ret;
                        },

                    ],
                    [
                        'label' => 'Логін',
                        'attribute' => 'username',
                        'headerOptions' => ['style' => 'width: 7%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 7%; overflow: hidden'],
                        'content'=>function($data){
                            $ret = $data->username;
                            if (!empty($data->username)){
                                $ret = Html::a($ret, Url::to(['/adminxx/check/view-user', 'id' => $data->user_id ]));
                            }
                            return $ret;
                        },

                    ],
                    [
                        'label' => 'Користувач',
                        'headerOptions' => ['style' => 'width: 10%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 10%; overflow: hidden'],
                        'content'=>function($data){
                            return (isset($data->userDatas)) ? $data->userDatas->userFio: '';
                        },

                    ],
                    [
                        'label' => 'Підрозділ',
                        'headerOptions' => ['style' => 'width: 20%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 20%; overflow: hidden'],
                        'content'=>function($data){
                            return (isset($data->userDatas)) ? $data->userDatas->gunp: '';
                        },

                    ],
                    [
                        'label' => 'Напрямок',
                        'headerOptions' => ['style' => 'width: 15%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 15%; overflow: hidden'],
                        'content'=>function($data){
                            return (isset($data->userDatas)) ? $data->userDatas->userDirection: '';
                        },

                    ],
                    [
                        'attribute' => 'createdAt',
                        'label' => 'Перший візіт ',
                        'headerOptions' => ['style' => 'width: 10%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 10%; overflow: hidden'],
                    ],
                    [
                        'attribute' => 'updatedAt',
                        'label' => 'Останній візіт ',
                        'headerOptions' => ['style' => 'width: 10%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 10%; overflow: hidden'],
                    ],
                    [
                        'attribute' => 'url',
                        'label' => 'Останній роут ',
                        'headerOptions' => ['style' => 'width: 15%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 15%; overflow: hidden'],
                    ],
                    [
                        'headerOptions' => ['style' => 'width:4%'],
                        'label'=>'Підрозділи',
                        'format'=>'text',
                        'content'=>function($data) use ($timeFix) {
                            return $data->getCgangedItemsCount('Department', $timeFix);
                        },
                    ],
                    [
                        'headerOptions' => ['style' => 'width:4%'],
                        'label'=>'Посади',
                        'format'=>'text',
                        'content'=>function($data) use ($timeFix) {
                            return $data->getCgangedItemsCount('Position', $timeFix);
                        },
                    ],
                    [
                        'headerOptions' => ['style' => 'width:4%'],
                        'label'=>'Персонал',
                        'format'=>'text',
                        'content'=>function($data) use ($timeFix) {
                            return $data->getCgangedItemsCount('Personal', $timeFix);
                        },
                    ],

                    /*
                    [
                        'headerOptions' => ['style' => 'width:4%'],
                        'label'=>'Підрозділи',
                        'format'=>'text',
                        'content'=>function($data) use ($timeFix) {
                            return $data->getCgangedItemsCount('Department', $timeFix);
                        },
                    ],
                    */

                    /*
                    [
                        'label'=>'Статус',
                        'content'=>function($data){
                            return \app\modules\adminxx\models\UserM::STATUS_DICT[$data->status];
                        },
                    ],
                    */
                    ['class' => 'yii\grid\ActionColumn',
                        'buttons'=>[
                            'view'=>function($url, $data) {
                                return Html::a('<span class="glyphicon glyphicon-eye"></span>', false,
                                    [
                                        'title' => '',
                                    ]);

                            },
                        ],
                        'template'=>' {view}',

                    ],
                    //------------------------------
                ],

            ]);
            Pjax::end() ?>

        </div>
    </div>

</div>




