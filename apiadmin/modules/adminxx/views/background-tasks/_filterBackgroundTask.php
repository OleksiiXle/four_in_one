<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use apiadmin\modules\adminxx\models\filters\BackgroundTaskFilter;

?>

<div class="container-fluid">
    <div class="xCard">

        <div class="row">
            <div class="col-md-12 col-lg-12 ">
                <div class="row">
                    <div class="col-md-12 col-lg-6">
                        <?php
                        $form = ActiveForm::begin([
                            'action' => ['index'],
                            'method' => 'post',
                            'id' => 'backgroundTaskFilterForm',
                        ]);
                        ?>
                        <div class="row>">
                            <?php
                            echo $form->field($filter, 'status', ['inputOptions' =>
                                ['class' => 'form-control', 'tabindex' => '4']])
                                ->dropDownList(BackgroundTaskFilter::getStatusesArray(),
                                    ['options' => [ $filter->status => ['Selected' => true]],]);

                            ?>
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
                            </div>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                    <div class="col-md-12 col-lg-6">
                        <?= Html::button('Файл лога общий', [
                                'class' => 'btn btn-primary',
                                'onclick' => "showLog('success');",
                        ]);?>
                        <?= Html::button('Файл лога помилок', [
                                'class' => 'btn btn-primary',
                                'onclick' => "showLog('error');",
                        ]);?>
                        <?= Html::button('Очистити зайвi завдання', [
                                'class' => 'btn btn-danger',
                                'onclick' => "showLog('deleteUnnecessaryTasks');",
                        ]);?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function cleanFilter(){
     //   $("#backgroundtaskfilter-status"). attr('value', '');

        document.getElementById('backgroundtaskfilter-status').value = null;
      //  document.getElementById('userfilter-treedepartment_id').value = 14005;
        $("#subBtn").click();
    }

</script>


