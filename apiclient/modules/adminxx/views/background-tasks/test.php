<?php
echo \yii\helpers\Html::button('start', ['id' => 'bacgroundTaskStartBtn']);
echo \common\widgets\backgroundTask\BackgroundTaskWidget::widget([
    'model' => \console\backgroundTasks\tasks\TestTaskWorker::class,
    'arguments' => [
        'id' => 7778,
    ],
]);
?>

