<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = \Yii::t('app', 'Новый пароль');
?>

<div class="site-reset-password">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="row">
        <div class="col-lg-5">

            <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
            <?= $form->field($model, 'password')->passwordInput(['autofocus' => true]) ?>
            <?= $form->field($model, 'retypePassword')->passwordInput() ?>
            <div class="form-group">
                <?= Html::submitButton(\Yii::t('app', 'Сохранить'), ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>