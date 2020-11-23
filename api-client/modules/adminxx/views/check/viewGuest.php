<?php
use yii\helpers\Html;
use \yii\widgets\DetailView;

$this->title = 'Профіль відвідувача';

?>
<div class="container-fluid">

    <div class="row xHeader">
        <div class="col-md-12 col-lg-10">
        </div>
        <div class="col-md-12 col-lg-2">
            <h4><?= Html::a('Повернутися', 'guest-control', ['style' => 'color:red']);?></h4>
        </div>

    </div>
    <div class="row xContent">
        <div class="xCard" style="min-height: 70vh">
            <?php
            echo DetailView::widget([
                'model' => $guest,
                'attributes' => [
                    [
                        'attribute' => 'remote_ip',
                        'value' => function($data){
                            return $data['remote_ip'];
                        }
                    ],
                    [
                        'attribute' => 'referrer',
                        'value' => function($data){
                            return $data['referrer'];
                        }
                    ],
                    [
                        'attribute' => 'remote_host',
                        'value' => function($data){
                            return $data['remote_host'];
                        }
                    ],
                    [
                        'attribute' => 'absolute_url',
                        'value' => function($data){
                            return $data['absolute_url'];
                        }
                    ],
                    'createdAt',
                    'updatedAt',
                    [
                        'attribute' => 'url',
                        'value' => function($data){
                            return $data['url'];
                        }
                    ],

                ],
            ]);
            ?>


        </div>
    </div>
</div>
