<?php

use \yii\helpers\Html;

$this->title = \Yii::t('app', 'Активність користувачів');
//\app\modules\adminxx\assets\AdminxxUserActivityAsset::register($this);


?>
<?php
$interval = (empty($dataProvider->filterModel->getAttributes()['activityInterval']))
      ? 3600
      : $dataProvider->filterModel->getAttributes()['activityInterval'];
$timeFix = time() - $interval;

?>

<div class="container-fluid">

    <div class="row xContent">
        <div class="xCard">
            <?php
            echo \common\widgets\xlegrid\Xlegrid::widget([
                'dataProvider' => $dataProvider,
                'gridTitle' => '' . \common\models\UserData::$activityIntervalArray[$interval] ,
                'additionalTitle' => 'qq',
                'filterView' => '@app/modules/adminxx/views/check/_filterUserActivity',
                //-------------------------------------------
                'tableOptions' => [
                    'tableOptions' => [
                        'class' => 'table table-bordered table-hover table-condensed',
                        'style' => ' width: 100%; table-layout: fixed;',
                    ],
                ],
                //-------------------------------------------
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn',
                        'headerOptions' => ['style' => 'width:1%'],
                    ],
                    [
                        'attribute' => 'lastRoutTime',
                        'headerOptions' => ['style' => 'width:4%'],
                    ],
                    [
                        'attribute' => 'userFio',
                        'headerOptions' => ['style' => 'width:8%'],
                    ],
                    [
                        'attribute'=>'userLogin',
                        'headerOptions' => ['style' => 'width:4%'],
                        'content'=>function($data) use ($timeFix){
                            //    return Html::a($data->userLogin, '/adminxx/user/update?id=' . $data->user_id);
                            return Html::a($data->userLogin, false,
                                [
                                    // 'onclick' => "modalOpenUserActivityInfo($data->user_id, $timeFix);",
                                ]);
                        },
                    ],
                    [
                        'attribute' => 'last_rout',
                        'headerOptions' => ['style' => 'width:4%'],
                    ],
                    [
                        'attribute' => 'lastRoutTime',
                        'headerOptions' => ['style' => 'width:4%'],
                    ],
                ],

            ]);
            ?>
        </div>


    </div>


</div>


<?php //***********************************  заготовки под модальные окна
//----- большое окно
yii\bootstrap\Modal::begin([
    'headerOptions' => ['id' => 'modalHeader_lg','class'=>'text-center'],
    'id' => 'main-modal-lg',
    'closeButton' => ['tag' => 'button', 'label' => '&times;'],
    'size' => 'modal-lg',
    //keeps from closing modal with esc key or by clicking out of the modal.
    // user must click cancel or X to close
    'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
    // 'options'=>['style'=>'width:1200px']
]);?>
<div id='modalContent_lg'></div>

<?php yii\bootstrap\Modal::end();?>


