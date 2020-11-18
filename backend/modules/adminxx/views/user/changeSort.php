<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>
<div class="site-login">
    <h3><?= Html::encode('Зміна типу сортування') ?></h3>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(); ?>
            <?= $form->field($model, 'positionSort', ['inputOptions' =>
                ['class' => 'form-control', 'tabindex' => '1']])
                ->dropDownList(\backend\modules\adminxx\models\form\ChangeSort::$positionSorts,
                    ['options' => [ $model->positionSort => ['Selected' => true]],]);?>
            <div class="form-group">
                <?= Html::submitButton('Змінити', ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
