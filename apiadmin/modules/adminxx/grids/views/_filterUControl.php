<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use apiadmin\modules\adminxx\models\UserData;
?>
<div class="user-search container-fluid" >
    <?php
    $form = ActiveForm::begin();
    ?>
    <div class="xCard">
        <div class="row">
            <div class="col-md-12 col-lg-6 ">
                <div class="xCard">
                    <?php
                    echo $form->field($filter, 'username');
                    echo $form->field($filter, 'userFam');
                    echo $form->field($filter, 'remote_ip');
                    echo $form->field($filter, 'activityInterval')
                        ->dropDownList(UserData::$activityIntervalArray,
                            ['options' => [ $filter->activityInterval => ['Selected' => true]],]);
                    ?>
                </div>
            </div>
            <div class="col-md-12 col-lg-6 ">
                <div class="xCard">
                    <?php
                    echo $form->field($filter, 'showUsers')->checkbox(['class' => 'showItem']);
                    echo $form->field($filter, 'showGuests')->checkbox(['class' => 'showItem']);
                    ?>
                </div>
                <div class="xCard">
                    <?php
                    echo $form->field($filter, 'ipWithoutUser')->checkbox();
                    echo $form->field($filter, 'showOnlyChecked')->checkbox();
                    echo $form->field($filter, 'allRowsAreChecked')->hiddenInput()->label(false);
                    ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group" align="center" style="padding: 20px">
                <?= Html::a('Видалити всі данні користувачів та відвідувачів', Url::toRoute(['/adminxx/check/delete-visitors', 'mode' => 'deleteAll']),
                    [
                        'class' => 'btn btn-danger',
                        'data-confirm' => 'Are you sure?',
                        'data-method' => 'post',
                    ]);?>
                <?= Html::a('Видалити застарілі данні відвідувачів', Url::toRoute(['/adminxx/check/delete-visitors', 'mode' => 'deleteOldGuests']),
                    [
                        'class' => 'btn btn-danger',
                        'data-confirm' => 'Are you sure?',
                        'data-method' => 'post',
                    ]);?>
                <?= Html::a('Видалити данні всіх відвідувачів', Url::toRoute(['/adminxx/check/delete-visitors', 'mode' => 'deleteAllGuests']),
                    [
                        'class' => 'btn btn-danger',
                        'data-confirm' => 'Are you sure?',
                        'data-method' => 'post',
                    ]);?>
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
                    'onclick' => 'cleanFilter(true);',
                ]) ?>
            </div>
        </div>

    </div>
    <?php ActiveForm::end(); ?>

</div>


