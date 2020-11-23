<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use frontend\models\LogoutForm;
?>
<div class="container">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to login:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                <?= $form->field($model, 'provider')->dropDownList(LogoutForm::providers(),
                    ['options' => [ $model->provider => ['Selected' => true]],])
                    ->label('API-provider') ?>

                <div class="form-group">
                    <?= Html::submitButton('Reset tokens', ['class' => 'btn btn-primary']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
