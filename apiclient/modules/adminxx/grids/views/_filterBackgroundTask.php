<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\modules\adminxx\grids\filters\BackgroundTaskFilter;

?>
<style>
    .btn{
        width: 250px;
        margin: 20px;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-6">
            <div class="xCard">
                <?php $form = ActiveForm::begin();?>
                <div class="row>">
                    <?php
                    echo $form->field($filter, 'status', ['inputOptions' =>
                        ['class' => 'form-control', 'tabindex' => '4']])
                        ->dropDownList(BackgroundTaskFilter::getStatusesArray(),
                            ['options' => [ $filter->status => ['Selected' => true]],]);
                    echo $form->field($filter, 'showOnlyChecked')->checkbox();
                    echo $form->field($filter, 'allRowsAreChecked')->hiddenInput()->label(false);

                    ?>
                </div>
                <div class="row">
                    <div class="form-group" align="center" style="padding: 20px">
                        <?php
                        echo  Html::button(Yii::t('app', 'Применить фильтр'), [
                            'class' => 'btn btn-primary',
                            'onclick' => 'useFilter();'
                        ]);
                        ?>
                        <?= Html::button(Yii::t('app', 'Очистить фильтр'), [
                            'class' => 'btn btn-danger',
                            'onclick' => 'cleanFilter(true);',
                        ]) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="xCard">
                <?= Html::button('Файл лога общий', [
                    'class' => 'btn btn-primary',
                    'onclick' => "showLog('success');",
                ]);?>
                <br>
                <?= Html::button('Файл лога помилок', [
                    'class' => 'btn btn-primary',
                    'onclick' => "showLog('error');",
                ]);?>
                <br>
                <?= Html::button('Очистити зайвi завдання', [
                    'class' => 'btn btn-danger',
                    'onclick' => "showLog('deleteUnnecessaryTasks');",
                ]);?>
            </div>
        </div>
    </div>
</div>