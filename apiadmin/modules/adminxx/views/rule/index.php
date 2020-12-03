<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;


$this->title =  'Правила';
?>
<div class="col-md-12 xContent">


    <p>
        <?= Html::a('Нове правило', \yii\helpers\Url::toRoute('create'), ['class' => 'btn btn-success']) ?>
    </p>
    <div class="xCard">
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => [
                'class' => 'table table-bordered table-hover table-condensed',
            ],

            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'name',
                ['class' => 'yii\grid\ActionColumn',
                    'template'=>'{delete}',

                ],

            ],
        ]);
        ?>

    </div>


</div>
