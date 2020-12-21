<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

$this->title = \Yii::t('app', 'New oauth client')

?>

<div class="container-fluid">
    <h3><?= Html::encode($this->title) ?></h3>
    <?php
    ?>
    <div class="col-md-3">
        <?php $form = ActiveForm::begin([
            'id' => 'form-update',
        ]); ?>
        <?= Html::errorSummary($model)?>
        <?php
        echo $form->field($model, 'client_id');
        echo $form->field($model, 'client_secret');
        echo $form->field($model, 'redirect_uri');
        echo $form->field($model, 'grant_type');
        ?>
        <div class="form-group" align="center">
            <?= Html::submitButton(\Yii::t('app', 'Создать'), ['class' => 'btn btn-primary', ]) ?>
            <?= Html::a(\Yii::t('app', 'Отмена'), Url::toRoute('/adminxx/oauth'),[
                'class' => 'btn btn-danger', 'name' => 'reset-button'
            ]);?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>


