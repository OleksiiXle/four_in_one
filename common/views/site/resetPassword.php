<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('app', 'Смена пароля');
?>

<div class="container">
    <div class="row">
        <div class="col-lg-4 col-lg-offset-3">
            <div class="xCard">
                <?php $form = ActiveForm::begin(['id' => 'changePasswordForm']); ?>
                <?= $form->field($model, 'newPassword')->passwordInput() ?>
                <?= $form->field($model, 'retypePassword')->passwordInput() ?>
                <?php ActiveForm::end(); ?>
            </div>
            <div class="form-group" align="center">
                <?= Html::submitButton(Yii::t('app', 'Сохранить'), [
                    'name' => 'register-button',
                    'class' => 'btn btn-primary',
                    'form' => 'changePasswordForm',
                ]) ?>
            </div>

        </div>
    </div>
</div>
