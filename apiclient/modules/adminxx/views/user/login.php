<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>
<div class="site-login">
    <h3><?= Html::encode('Вхід') ?></h3>

    <div class="container">
        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
        <?= $form->field($model, 'username') ?>
        <?= $form->field($model, 'password')->passwordInput() ?>
        <?php
        /*
        echo $form->field($model, 'reCaptcha')->widget(
            \himiklab\yii2\recaptcha\ReCaptcha::className(),
            ['siteKey' => '6LfU-p8UAAAAAOSjC2aMujiIuD9K8zw7tP4IJQrp']
        )->label(false);
        */
        ?>
        <div class="form-group">
            <?= Html::submitButton('Вхід', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>
        <div class="form-group">
            <?= Html::a('Забув пароль', \yii\helpers\Url::toRoute('forget-password'));?>
        </div>
        <?php ActiveForm::end(); ?>
   </div>
</div>
