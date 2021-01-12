<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\UserData;
?>
<div class="container-fluid" >
    <?php
    $form = ActiveForm::begin([
            'options' => [
                    'id' => 'postFilterForm'
            ]
    ]);
    ?>
    <div class="xCard">
        <div class="row">
            <div class="col-md-12 col-lg-6 ">
                <div class="xCard">
                    <?php
                    echo $form->field($filter, 'name');
                    //todo***************** обязательные поля
                    echo $form->field($filter, 'showOnlyChecked')->checkbox();
                    echo $form->field($filter, 'allRowsAreChecked')->hiddenInput()->label(false);
                    ?>
                </div>
            </div>
            <div class="col-md-12 col-lg-6 ">
            </div>
        </div>
        <div class="row">
            <div class="form-group" align="center" style="padding: 20px">
                <?php
                //  echo  Html::submitButton('Шукати', ['class' => 'btn btn-primary', 'id' => 'subBtn']);
                echo  Html::button(Yii::t('app', 'Применить фильтр'), [
                    'class' => 'btn btn-primary',
                    'id' => 'subBtn',
                    'onclick' => 'useFilter();'
                ]);
                ?>
                <?= Html::button(Yii::t('app', 'Очистить фильтр'), [
                    'class' => 'btn btn-danger',
                    'id' => 'cleanBtn',
                  //  'onclick' => 'cleanPostFilter();',
                    'onclick' => 'cleanFilter(true);',
                ]) ?>
           </div>
        </div>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<script>
    function cleanPostFilter() {
        cleanFilter(false);
        $("#postFilterForm").submit();
    }
</script>


