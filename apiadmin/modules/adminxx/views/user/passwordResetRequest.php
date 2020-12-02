<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = \Yii::t('app', 'Запрос на восстановление пароля');
?>

<div class="site-request-password-reset">
    <h3><?= Html::encode($this->title) ?></h3>
    <div class="row">
        <div class="col-lg-5">

            <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
            <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>
            <div class="form-group">
                <?= Html::submitButton(\Yii::t('app', 'Отправить'), ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>