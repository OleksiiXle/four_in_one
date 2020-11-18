<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="col-md-12 xContent">
    <div class="xCard">
        <?php $form = ActiveForm::begin(); ?>


        <?= $form->field($model, 'className', ['inputOptions' =>
            ['class' => 'form-control', 'tabindex' => '2']])
            ->dropDownList($rulesClasses);?>



        <div class="form-group" align="center">
            <?php
            echo Html::submitButton($model->isNewRecord ? \Yii::t('app', 'Создать')
                :\Yii::t('app', 'Отмена'), [
                'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
                'name' => 'doAction',
                'value' => '1',
                'style' => 'margin: 15px'
            ]);

            echo Html::submitButton(\Yii::t('app', 'Отмена'), [
                'class' => 'btn btn-danger',
                'name' => 'goBack',
                'value' => '1',
                'style' => 'margin: 15px'


            ])
            ?>
        </div>


    </div>

    <?php ActiveForm::end(); ?>
</div>
