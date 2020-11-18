<?php
use common\components\models\Configs;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Налаштування';
?>

<div class="container-fluid">
    <?php $form = ActiveForm::begin([
        'id' => 'form-update',
        'options' => [
        ],


    ]); ?>

    <?= Html::errorSummary($model)?>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <div class="xCard">
                <?php
                echo $form->field($model, 'rbacCacheSource')->dropDownList(Configs::dictionaryrRacCacheSource(),
                    ['options' => [ $model->rbacCacheSource => ['Selected' => true]]]);
                echo $form->field($model, 'permCacheKeyDuration')->dropDownList(Configs::dictionaryDurationSec(),
                    ['options' => [ $model->permCacheKeyDuration => ['Selected' => true]]]);
                echo $form->field($model, 'guestControlDuration')->dropDownList(Configs::dictionaryDuration(),
                    ['options' => [ $model->guestControlDuration => ['Selected' => true]]]);
                echo $form->field($model, 'adminEmail');
                echo $form->field($model, 'permCacheKey');
                echo $form->field($model, 'menuType')->dropDownList(Configs::dictionaryMenu(),
                    ['options' => [ $model->menuType => ['Selected' => true]]]);
                echo $form->field($model, 'passwordResetTokenExpire')->dropDownList(Configs::dictionaryDuration(),
                    ['options' => [ $model->passwordResetTokenExpire => ['Selected' => true]]]);
                echo $form->field($model, 'userDefaultRole')->dropDownList(Configs::dictionaryRoles(),
                    ['options' => [ $model->userDefaultRole => ['Selected' => true]]]);
                ?>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <div class="xCard">
                <?php
                // echo $form->field($model, 'userControl')->dropDownList(Configs::dictionaryYes(),
                //     ['options' => [ $model->userControl => ['Selected' => true]]]);
                // echo $form->field($model, 'guestControl')->dropDownList(Configs::dictionaryYes(),
                //      ['options' => [ $model->guestControl => ['Selected' => true]]]);
                echo $form->field($model, 'userControl')->checkbox();
                echo $form->field($model, 'guestControl')->checkbox();
                echo $form->field($model, 'signupWithoutEmailConfirm')->checkbox();
                ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group" align="center">
            <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
            <?= Html::submitButton(Yii::t('app', 'Отмена'), ['class' => 'btn btn-danger', 'name' => 'reset-button']) ?>

        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>







