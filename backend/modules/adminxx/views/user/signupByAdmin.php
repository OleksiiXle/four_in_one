<!--*************** форма регистрации пользователя администратором -->
<!--*************** рендерится из UserController -> actionSignupByAdmin($invitation = false) -->
<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\modules\adminxx\assets\AdminxxUpdateUserAsset;
use common\widgets\selectMultiXle\SelectMultiXleWidget;

AdminxxUpdateUserAsset::register($this);

if ($model->isNewRecord){
    $update = false;
    $disable = '';
    $this->title = Yii::t('app', 'Регистрация нового пользователя');
} else {
    $update = true;
    $disable = 'disabled';
    $this->title = Yii::t('app', 'Изменение данных пользователя');
}

?>

<?php $form = ActiveForm::begin(['id' => 'form-update',]); ?>
<div class="container">
    <div class="row xHeader">
        <div class="col-md-12 col-lg-12">
            <?= Html::errorSummary($model)?>
            <?= $form->field($model, 'id')->hiddenInput()->label(false);?>
        </div>
    </div>

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
                <?= SelectMultiXleWidget::widget([
                    'modelName' => 'UserM',
                    'textAreaAttribute' => 'userRolesToSet',
                    'label' => Yii::t('app', 'Роли'),
                    'itemsArray' => $defaultRoles,
                ]);?>
                <?= $form->field($model, 'username')->textInput([]); ?>
                <?php
                if (!$update){
                    echo $form->field($model, 'password');
                    echo $form->field($model, 'retypePassword');
                    echo $form->field($model, 'invitation', ['inputOptions' =>
                        ['class' => 'form-control']])->dropDownList([0 => 'Нет', 1 => 'Да'],
                        ['options' => [0 => ['Selected' => true]],]);
                }
                ?>
            </div>
        </div>
    </div>
    <!--*************************************************************************** КНОПКИ СОХРАНЕНИЯ -->
    <div class="row xContent">
        <div class="col-md-12 col-lg-12">
            <div class="row">
                <div class="form-group" align="center">
                    <?= Html::submitButton(Yii::t('app', 'Сохранить'), [
                        'class' => 'btn btn-primary',
                    ]) ?>
                    <?= Html::a(Yii::t('app', 'Отмена'), '/adminxx/user',[
                        'class' => 'btn btn-danger', 'name' => 'reset-button'
                    ]);?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

