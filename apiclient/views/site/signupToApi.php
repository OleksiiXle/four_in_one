<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \apiuser\models\SignupForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\models\LoginForm;

$this->title = 'Signup';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to signup:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
                <?= $form->field($model, 'provider')->dropDownList(LoginForm::providers(),
                ['options' => [ $model->provider => ['Selected' => true]],])
                ->label('API-provider') ?>

                <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'email') ?>

                <?= $form->field($model, 'first_name') ?>

                <?= $form->field($model, 'middle_name') ?>

                <?= $form->field($model, 'last_name') ?>

                <?= $form->field($model, 'password')->passwordInput() ?>

                <?= $form->field($model, 'retypePassword')->passwordInput() ?>

                <div class="form-group">
                    <?= Html::submitButton('Signup', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="row" style="overflow: auto">
        <?=$model->errorContent?>
    </div>

</div>
