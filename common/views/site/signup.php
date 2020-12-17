<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('app', 'Регистрация');
?>

<div class="container">
    <div class="row xHeader">
        <div class="col-md-12 col-lg-12">
            <?= Html::errorSummary($model)?>
        </div>
    </div>
    <?php $form = ActiveForm::begin([
            'options' => [
                'id' => 'registrationForm',
                ]
    ]); ?>
    <div class="row xContent">
        <div class="col-sm-4 col-md-6 col-lg-6">
            <div class="xCard ">
                <?= $form->field($model, 'last_name')->textInput([]);?>
                <?= $form->field($model, 'first_name')->textInput([]);?>
                <?= $form->field($model, 'middle_name')->textInput([]); ?>
                <?= $form->field($model, 'email'); ?>
                <?= $form->field($model, 'phone'); ?>
            </div>
        </div>

        <div class="col-sm-4 col-md-6 col-lg-6">
            <div class="xCard ">
                <?= $form->field($model, 'username')->textInput([]); ?>
                <?= $form->field($model, 'password')->textInput([]); ?>
                <?= $form->field($model, 'retypePassword')->textInput([]); ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
    <!--*************************************************************************** КНОПКИ СОХРАНЕНИЯ -->
    <div class="row xContent">
        <div class="col-md-12 col-lg-12">
            <div class="row">
                <div class="form-group" align="center">
                    <?= Html::submitButton(Yii::t('app', 'Сохранить'), [
                        'name' => 'register-button',
                        'class' => 'btn btn-primary',
                        'form' => 'registrationForm',
                    ]) ?>
                    <?= Html::a(Yii::t('app', 'Отмена'), yii\helpers\Url::toRoute($this->context->action->id), [
                            'class' => 'btn btn-danger',
                            'name' => 'reset-button',
                            'data' => [
                                'method' => 'post',
                                    'params' => [
                                        'reset-btn' => 'true',
                                    ],
                                ],
                    ])?>
                </div>
            </div>
        </div>
    </div>
</div>
