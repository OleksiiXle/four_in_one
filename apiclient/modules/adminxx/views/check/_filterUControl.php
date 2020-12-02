<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use \yii\helpers\Url;
use \app\modules\adminxx\models\UserData;
use \app\modules\adminxx\models\UserDepartment;
?>
<div class="user-search container-fluid" >
    <?php
    $form = ActiveForm::begin([
        'action' => ['guest-control'],
        'method' => 'post',
        'id' => 'uControlFilterForm',
        // 'layout' => 'horizontal',
    ]);
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
                    echo $form->field($filter, 'userDirection', ['inputOptions' =>
                        ['class' => 'form-control', 'tabindex' => '4']])
                        ->dropDownList(UserData::$directionArray,
                            ['options' => [ $filter->userDirection => ['Selected' => true]],]);
                    echo $form->field($filter, 'userGunp', ['inputOptions' =>
                        ['class' => 'form-control', 'tabindex' => '4']])
                        ->dropDownList(UserDepartment::getDictionaryDepartment(),
                            ['options' => [ $filter->userGunp => ['Selected' => true]],]);

                    ?>
                </div>
            </div>
            <div class="col-md-12 col-lg-6 ">
                <div class="xCard">
                    <?php
                    echo $form->field($filter, 'showAll')->checkbox(['class' => 'checkBoxAll']);
                    echo $form->field($filter, 'showUsers')->checkbox(['class' => 'showItem']);
                    echo $form->field($filter, 'showGuests')->checkbox(['class' => 'showItem']);
                    ?>
                </div>
                <div class="xCard">
                    <?php
                    echo $form->field($filter, 'ipWithoutUser')->checkbox();
                    ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group" align="center" style="padding: 20px">
                <?= Html::submitButton(\Yii::t('app', 'Фильтр'), ['class' => 'btn btn-primary', 'id' => 'subBtn']) ?>
                <?= Html::button(\Yii::t('app', 'Очистить фильтр'), [
                    'class' => 'btn btn-danger',
                    'id' => 'cleanBtn',
                    'onclick' => 'cleanFilter();',
                ]) ?>
            </div>
        </div>
        <div class="row">
            <div class="form-group" align="center" style="padding: 20px">
                <?= Html::a('Видалити всі данні користувачів та відвідувачів', Url::to(['/adminxx/check/delete-visitors', 'mode' => 'deleteAll']),
                    [
                        'class' => 'btn btn-danger',
                        'data-confirm' => 'Are you sure?',
                        'data-method' => 'post',
                    ]);?>
                <?= Html::a('Видалити застарілі данні відвідувачів', Url::to(['/adminxx/check/delete-visitors', 'mode' => 'deleteOldGuests']),
                    [
                        'class' => 'btn btn-danger',
                        'data-confirm' => 'Are you sure?',
                        'data-method' => 'post',
                    ]);?>
                <?= Html::a('Видалити данні всіх відвідувачів', Url::to(['/adminxx/check/delete-visitors', 'mode' => 'deleteAllGuests']),
                    [
                        'class' => 'btn btn-danger',
                        'data-confirm' => 'Are you sure?',
                        'data-method' => 'post',
                    ]);?>
            </div>
        </div>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<script>
    function cleanFilter(){
        document.getElementById('ucontrolfilter-username').value = null;
        document.getElementById('ucontrolfilter-remote_ip').value = null;
        document.getElementById('ucontrolfilter-userfam').value = null;
        document.getElementById('ucontrolfilter-userdirection').value = 0;
        document.getElementById('ucontrolfilter-usergunp').value = 0;
        document.getElementById('ucontrolfilter-activityinterval').value = 0;

        $('#ucontrolfilter-ipwithoutuser').prop('checked', false);
        $('.showItem').prop('checked', false);
        $('.checkBoxAll').prop('checked', true);


        $("#subBtn").click();
    }

    $(".checkBoxAll").change(function() {
        if(this.checked) {
            $('.showItem').prop('checked', false);
        }
    });

    $(".showItem").change(function() {
        if(this.checked) {
            $('.checkBoxAll').prop('checked', false);
        }
    });

    $("#ucontrolfilter-ipwithoutuser").change(function() {
        if(this.checked) {
            $('.checkBoxAll').prop('checked', true);
            $('.showItem').prop('checked', false);
            document.getElementById('ucontrolfilter-username').value = null;
            document.getElementById('ucontrolfilter-remote_ip').value = null;
            document.getElementById('ucontrolfilter-userfam').value = null;
            document.getElementById('ucontrolfilter-userdirection').value = 0;
            document.getElementById('ucontrolfilter-usergunp').value = 0;
        }
    });

</script>


