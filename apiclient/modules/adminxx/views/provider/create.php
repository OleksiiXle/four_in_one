<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>

<div class="container-fluid">
    <h3><?= Html::encode($this->title ) ?></h3>
    <div class="row">
        <div class="col-md-12">
            <?php $form = ActiveForm::begin([
                'layout'=>'horizontal',
            ]); ?>
            <?= Html::errorSummary($model)?>
            <?php
            echo $form->field($model, 'name');
            echo $form->field($model, 'class');
            echo $form->field($model, 'client_id');
            echo $form->field($model, 'client_secret');
            echo $form->field($model, 'token_url');
            echo $form->field($model, 'auth_url');
            echo $form->field($model, 'signup_url');
            echo $form->field($model, 'api_base_url');
            echo $form->field($model, 'scope');
            echo $form->field($model, 'state_storage_class');
            ?>
            <div class="form-group" align="center">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Отмена', \yii\helpers\Url::toRoute('/adminxx/provider'),[
                    'class' => 'btn btn-danger', 'name' => 'reset-button'
                ]);?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
