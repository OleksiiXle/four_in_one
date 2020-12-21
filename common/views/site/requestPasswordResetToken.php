<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('app', 'Сброс пароля');
?>

<div class="container">
    <div class="row">
        <div class="col-lg-4 col-lg-offset-3">
            <h3><?= Html::encode($this->title) ?></h3>
            <div class="xCard">
                <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>
                <div class="form-group">
                    <?= Html::submitButton(Yii::t('app', 'Сменить пароль'), [
                            'class' => 'btn btn-primary']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
