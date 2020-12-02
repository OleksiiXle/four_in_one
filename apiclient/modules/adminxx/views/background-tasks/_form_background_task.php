<?php

use yii\helpers\Html;
use \yii\widgets\DetailView;


?>
<div class="container-fluid">
    <div class="row">
        <?php if ($mode === 'delete'): ?>
        <div class="form-group" align="center">
            <?= Html::button( 'Видалити',
                ['class' =>  'btn btn-danger',
                    'onclick' => "deleteBackgroundTask(" . $task->id . ");",
                ]) ?>
            <?= Html::button( 'Відміна',
                ['class' =>  'btn btn-success',
                    'onclick' => 'hideModal();',
                ]) ?>
        </div>
        <?php endif;?>
    </div>

    <div class="row">
        <div class="xCard">
            <?php
            echo DetailView::widget([
                'model' => $task,
                'attributes' => [
                    'id',
                    'pid',
                    'user_id',
                    'model',
                    'arguments',
                    'status',
                    [
                        'label' => 'Статус по PID',
                        'format' => 'html',
                        'value' => function($data)
                        {
                            return ($data->isRunning)
                                ? "<span class='blink_text_no_active_waiting_to_active' >Працюе</span>"
                                : "<span>Не працюе</span>";
                        }
                    ],
                    'result_file',
                   // 'result_file_pointer',
                    'progress',
                    //'result',
                    'datetime_create',
                    'datetime_update',
                ],
            ]);
            ?>
        </div>
    </div>
    <div class="row">
        <div class="xCard">
            <h3>Результат</h3>
            <?=str_replace(PHP_EOL, '<br>', $task->result);?>
        </div>
    </div>
    <div class="row">
        <div class="xCard">
            <?=$resultContent;?>
        </div>
    </div>

</div>
