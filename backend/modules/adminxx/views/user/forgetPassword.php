<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Забули пароль?';
?>
<div class="site-forget-password">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Будь ласка, введіть Ваш email або логін</p>

    <div class="row">
        <?php if (Yii::$app->session->hasFlash('newPwdSended')): ?>
            <div class="alert alert-success">
                На Вашу пошту відіслано новий пароль.
            </div>
        <?php elseif (Yii::$app->session->hasFlash('userNotFound')): ?>
            <div class="alert alert-danger">
                Користувача з такими даними не існує.
            </div>
        <?php else: ?>
            <div class="col-lg-5">
                <?php $form = ActiveForm::begin(['id' => 'forget-password-form']); ?>
                <?= $form->field($model, 'username')->textInput() ?>
                <?= $form->field($model, 'email')->textInput() ?>
                <div class="form-group">
                    <?= Html::submitButton('Відправити', ['class' => 'btn btn-primary']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
