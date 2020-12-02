<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;


$this->title = \Yii::t('app', 'Настройки');
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6" align="left">
            <h3><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="col-md-6" align="right" style="padding-top: 20px">
            <?php
            echo Html::a(\Yii::t('app', 'Изменить'), '/adminxx/configs/update', [
                'class' =>'btn btn-primary',
            ]);
            ?>
        </div>

    </div>
    <div class="row">
        <?php Pjax::begin(['id' => 'gridConfigs']);
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            //-------------------------------------------
            'tableOptions' => [
                'class' => 'table table-bordered table-striped ',
            ],
            //-------------------------------------------
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
               // 'typeTxt',
                'owner',
                'name',
                'content',
                /*
                ['class' => 'yii\grid\ActionColumn',
                    'buttons'=>[
                        ''=>function($url, $data) {
                            return Html::a('<span class="glyphicon glyphicon-pensil"></span>', ['/adminxx/configs/update', 'id' => $data['id'] ],
                                [
                                    'title' => \Yii::t('app', 'Изменить'),
                                ]);

                        },
                        'delete'=>function($url, $data) {
                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/adminxx/configs/delete', 'id' => $data['id'] ],
                                [
                                    'title' => \Yii::t('app', 'Удалить'),
                                    'data-confirm' => 'Подтвердите удаление',
                                    'data-method' => 'post',
                                ]);

                        },
                    ],
                    'template'=>' {update}  {delete}',


                ],
                */
                //------------------------------
            ],


        ]);
        Pjax::end() ?>
    </div>

</div>




