<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

?>
<div class="site-login">

    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-lg-offset-3">
                <h3><?= Html::encode(Yii::t('app', 'Вход')) ?></h3>
                <div class="xCard">
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
                        <?= Html::submitButton(Yii::t('app', 'Вход'), ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                    </div>
                    <div class="form-group">
                        <?= Html::a(Yii::t('app', 'Забыл пароль'), Url::toRoute('request-password-reset'));?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
   </div>
</div>
