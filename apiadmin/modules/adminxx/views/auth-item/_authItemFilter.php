<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>

<div class="auth-item-search" >
    <?php
    $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'post',
        'id' => 'authItemFilterForm',
        'layout' => 'horizontal',
    ]);
    ?>
    <div class="row">
        <div class="col-md-4">
            <?php
            echo $form->field($filter, 'type')
                ->dropDownList(\apiadmin\modules\adminxx\models\AuthItemX::getTypeDict(),
                    ['options' => [ $filter->type => ['Selected' => true]],]);
            ?>
        </div>
        <div class="col-md-4">
            <?php
            echo $form->field($filter, 'name');
            ?>
        </div>
        <div class="col-md-4">
            <?php
            echo $form->field($filter, 'rule_name')
                ->dropDownList(\apiadmin\modules\adminxx\models\AuthItemX::getRulesList(),
                    ['options' => [ $filter->rule_name => ['Selected' => true]],]);
            ?>
        </div>
    </div>
    <div class="row">
        <div class="form-group" align="center">
            <?= Html::submitButton(\Yii::t('app', 'Фильтр'), ['class' => 'btn btn-danger']) ?>

        </div>
    </div>
    <input name="modelName" value="AuthItemFilter" hidden>

    <?php ActiveForm::end(); ?>

</div>
