<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

\app\modules\adminxx\assets\AdminxxUpdateUserAssignmentsAsset::register($this);

$this->title = 'Зміна ролей та дозвілів користувача ' . $model->username;

$_assigments = \yii\helpers\Json::htmlEncode($assigments);
$this->registerJs("
    var _assigments = {$_assigments};
",\yii\web\View::POS_HEAD);

?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="userAssigments xCard">
                <div class="col-md-5">
                    <div id="roles">
                        <div class="col-md-5 userSelect">
                            <h5>Доступні ролі</h5>
                            <select multiple size="25" class="form-control list" data-target="avaliableRoles"></select>
                        </div>
                        <div class="col-md-2 userSelect">
                            <br><br>
                            <?= Html::a('&gt;&gt;' , false, [
                                'class' => 'btn btn-success btn-assign actionAssign',
                                'data-rout' => \yii\helpers\Url::toRoute('/adminxx/assignment/assign'),
                                'data-user_id' => $user_id,
                                'data-target' => 'avaliableRoles',
                                'title' => 'Додати'
                            ]) ?><br><br>
                            <?= Html::a('&lt;&lt;' , false, [
                                'class' => 'btn btn-danger btn-assign actionRevoke',
                                'data-rout' => \yii\helpers\Url::toRoute('/adminxx/assignment/revoke'),
                                'data-user_id' => $user_id,
                                'data-target' => 'assignedRoles',
                                'title' =>'Скасувати'
                            ]) ?>
                        </div>
                        <div class="col-md-5 userSelect">
                            <h5><b>Призначені ролі</b></h5>
                            <select multiple size="25" class="form-control list" data-target="assignedRoles"></select>
                        </div>
                        <?php
                        //  require(__DIR__ . '/../ajax/_roleGrid.php');
                        ?>
                    </div>
                </div>
                <div class="col-md-7">
                    <div id="permissions">
                        <div class="col-md-5 userSelect">
                            <h5>Доступні дозвіли</h5>
                            <select multiple size="25" class="form-control list" data-target="avaliablePermissions"></select>
                        </div>
                        <div class="col-md-1 userSelect">
                            <br><br>
                            <?= Html::a('&gt;&gt;' , false, [
                                'class' => 'btn btn-success btn-assign actionAssign',
                                //  'data-rout' => '/adminxx/assignment/assign',
                                'data-user_id' => $user_id,
                                'data-target' => 'avaliablePermissions',
                                'title' =>  'Додати'
                            ]) ?><br><br>
                            <?= Html::a('&lt;&lt;' ,  false, [
                                'class' => 'btn btn-danger btn-assign actionRevoke',
                                // 'data-rout' => '/adminxx/assignment/revoke',
                                'data-user_id' => $user_id,
                                'data-target' => 'assignedPermissions',
                                'title' => 'Скасувати'
                            ]) ?>
                        </div>
                        <div class="col-md-6 userSelect">
                            <h5><b>Призначені дозвіли</b></h5>
                            <select multiple size="25" class="form-control list" data-target="assignedPermissions"></select>
                        </div>
                        <?php
                        //  require(__DIR__ . '/../ajax/_roleGrid.php');
                        ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 ">
            <div class="xCard">
                <?php $form = ActiveForm::begin([
                    'id' => 'form-update',
                ]); ?>
                <?= Html::errorSummary($model)?>
                <?php
                echo $form->field($model, 'status', ['inputOptions' =>
                    ['class' => 'form-control', 'tabindex' => '1']])
                    ->dropDownList(\app\modules\adminxx\models\UserM::getStatuses(),
                        ['options' => [ $model->status => ['Selected' => true]],])->label('Змінити статус') ;
                ?>
                <div class="form-group" align="center">
                    <?= Html::submitButton('Зберігти', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                    <?= Html::a('Відміна', \yii\helpers\Url::toRoute('/adminxx/user'),[
                        'class' => 'btn btn-danger', 'name' => 'reset-button'
                    ]);?>
                </div>
                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>
