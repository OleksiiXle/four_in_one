<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>


<div class="container-fluid">
    <?php
    $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'post',
        'id' => 'providerFilterForm',
    ]);
    ?>
    <div class="xCard">
    </div>
    <?php ActiveForm::end(); ?>
</div>
