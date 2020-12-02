<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;


$this->title = 'Зміна паролю';
?>
<div class="site-signup">
    <h3><?= Html::encode($this->title) ?></h3>
    <?= Html::errorSummary($model)?>


    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-change']); ?>
            <?= $form->field($model, 'oldPassword')->passwordInput() ?>
            <?= $form->field($model, 'newPassword')->passwordInput() ?>
            <?= $form->field($model, 'retypePassword')->passwordInput() ?>
                <div class="form-group">
                    <?= Html::submitButton('Зберігти', ['class' => 'btn btn-primary', 'name' => 'change-button']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
