<?php
echo \yii\helpers\Html::button('start', ['id' => 'bacgroundTaskStartBtn']);
echo \app\widgets\backgroundTask\BackgroundTaskWidget::widget([
    'model' => \app\commands\backgroundTasks\tasks\TestTaskWorker::class,
    'arguments' => [
        'id' => 7778,
    ],
]);
?>

