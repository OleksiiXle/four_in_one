<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\modules\adminxx\assets\AdminxxUserFilterAsset;

AdminxxUserFilterAsset::register($this);


$_exportQuery = \yii\helpers\Json::htmlEncode($exportQuery);
$this->registerJs("
    var _exportQuery      = {$_exportQuery};
",\yii\web\View::POS_HEAD);

?>


<div class="container-fluid">
    <?php
    $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'post',
        'id' => 'userFilterForm',
    ]);
    ?>
    <div class="xCard">

        <div class="row">
            <div class="col-md-12 col-lg-6 ">
                <div class="row">
                    <div class="col-md-12 col-lg-6">
                        <?php
                        echo $form->field($filter, 'checkedIdsJSON')->textarea([
                                'cols' => 30, 'rows' => '3',
                        ])->hiddenInput()->label(false);
                        echo $form->field($filter, 'username');
                        echo $form->field($filter, 'last_name');
                        echo $form->field($filter, 'first_name');
                        echo $form->field($filter, 'middle_name');
                        echo $form->field($filter, 'emails');
                        ?>
                    </div>
                    <div class="col-md-12 col-lg-6">
                        <?php
                        echo $form->field($filter, 'role', ['inputOptions' =>
                            ['class' => 'form-control', 'tabindex' => '4']])
                            ->dropDownList($filter->roleDict,
                                ['options' => [ $filter->role => ['Selected' => true]],]);
                        echo $form->field($filter, 'datetime_range');
                        echo $form->field($filter, 'datetime_min')->hiddenInput()->label(false);
                        echo $form->field($filter, 'datetime_max')->hiddenInput()->label(false);

                        ?>
                        <div>
                            <?php
                            echo $form->field($filter, 'showStatusActive')->checkbox(['class' => 'showStatus']);
                            echo $form->field($filter, 'showStatusInactive')->checkbox(['class' => 'showStatus']);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-6 ">
                <?php
                echo $form->field($filter, 'showOnlyChecked')->checkbox([
                      //  'onchange' => 'checkOnlyChecked(this);'
                ]);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="form-group" align="center" style="padding: 20px">
                <?php
              //  echo  Html::submitButton('Шукати', ['class' => 'btn btn-primary', 'id' => 'subBtn']);
                echo  Html::button('Шукати', [
                    'class' => 'btn btn-primary',
                    'id' => 'subBtn',
                    'onclick' => 'useFilter();'
                    ]);
                ?>
                <?= Html::button('Очистити фільтр', [
                    'class' => 'btn btn-danger',
                    'id' => 'cleanBtn',
                    'onclick' => 'cleanFilter(true);',
                ]) ?>
                <?= Html::button('В файл', [
                    'class' => 'btn btn-success',
                    'onclick' => 'startBackgroundUploadTask();',
                ]) ?>
            </div>
        </div>

    </div>
    <?php ActiveForm::end(); ?>
</div>

<script>

/*
    $(".checkBoxAll").change(function() {
        if(this.checked) {
            $('.showStatus').prop('checked', false);
        }
    });
    $(".showStatus").change(function() {
        if(this.checked) {
            $('.checkBoxAll').prop('checked', false);
        }
    });
    */
$(document).ready(function(){

});
moment.locale('ru');
$(function() {
  //  console.log(filterQueryObject);
    var daterangepicker_config = daterangepicker_default_config;
    if (filterQueryObject.hasOwnProperty('datetime_max') && filterQueryObject.hasOwnProperty('datetime_min')) {
        daterangepicker_config.startDate = filterQueryObject.datetime_min;
        daterangepicker_config.endDate = filterQueryObject.datetime_max;
    }
    $('input[name="UserFilter[datetime_range]"]').daterangepicker(daterangepicker_config, function (start, end,) {
        $('input[name="UserFilter[datetime_min]"]').val(start.format(datetime_format));
        $('input[name="UserFilter[datetime_max]"]').val(end.format(datetime_format));
    });
  //  $('input[name="UserFilter[datetime_range]"]').daterangepicker(daterangepicker_single_default_config);
});


</script>


