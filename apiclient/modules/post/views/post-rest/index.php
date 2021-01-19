<?php

use app\widgets\restGrid\RestGrid;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Тест');

?>

<div class="row xContent">
    <div class="xCard">
        <?= RestGrid::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'id',
                'name',
                'type',
                [
                    'label' => 'Картинка',
                    'format' => 'raw',
                    'value' => function($data){
                        return Html::img($data['mainImage'],[
                            'alt'=>'yii2 - картинка в gridview',
                            'style' => 'width:40px;'
                        ]);
                    },
                ],
                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
        <?php
      //  echo var_dump($ret);
        ?>
    </div>
</div>
