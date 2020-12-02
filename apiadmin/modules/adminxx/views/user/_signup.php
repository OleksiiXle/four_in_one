<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use \yii\helpers\Url;
use \app\widgets\changePosDep\ChangePosDepWidget;

\apiadmin\modules\adminxx\assets\AdminxxSingupAsset::register($this);
//\app\modules\adminxx\assets\AdminxxMdlAsset::register($this);

if ($model->isNewRecord){
    $update = false;
    $disable = '';
    $this->title = 'Рєєстрація нового користувача';
} else {
    $update = true;
    $disable = 'disabled';
    $this->title = 'Зміна даних користувача';
}


$_userDepartments = \yii\helpers\Json::htmlEncode($userDepartments);
$_userRoles = \yii\helpers\Json::htmlEncode($userRoles);
$this->registerJs("
    var _userDepartments = {$_userDepartments};
    var _userRoles = {$_userRoles};
",\yii\web\View::POS_HEAD);
//$this->registerJs($this->render('signup.js'));

?>
<style>
    .userCardArea{
        margin-top: 10px;
        margin-bottom: 10px;
        background-color: lightgrey;
        padding: 10px;
    }
    .userDepartmentsArea{
        margin-top: 10px;
        margin-bottom: 10px;
        background-color: aliceblue;
        padding: 10px;

    }
    .selectRoleArea{
        padding: 10px;

    }
    .userRolesArea{
        margin-top: 10px;
        background-color: lemonchiffon;
        padding: 10px;

    }
    .formButtons{
        margin-top: 10px;
        padding: 10px;
    }
</style>


<?php $form = ActiveForm::begin(['id' => 'form-update',]); ?>

<div class="row xHeader">
    <div class="col-md-12 col-lg-12">
        <h3><?= Html::encode($this->title) ?></h3>
        <?= Html::errorSummary($model)?>
        <?= $form->field($model, 'personal_id')->hiddenInput()->label(false);?>
        <?= $form->field($model, 'id')->hiddenInput()->label(false);?>

    </div>
</div>

<!--*************************************************************************** ЗОНА ДАННЫХ-->
<div class="row xContent">

    <!--*************************************************************************** КАРТОЧКА ПОЛЬЗОВАТЕЛЯ -->
    <div class="col-md-12 col-lg-6">
        <div class="xCard ">
            <div class="row">
                <div class="col-md-12 col-lg-6">
                    <div class="row>">
                        <div class="col-md-10" style="padding: 0;">
                            <?= $form->field($model, 'spec_document')->textInput([
                                //  'disabled' => $update,
                                //'onchange' => 'checkSpecDocument();'
                            ])->label('Жетон'); ?>
                        </div>
                        <div class="col-md-2">
                            <?php
                            //  if (!$update){
                            if (1 == 1){
                                echo Html::a('<span class="glyphicon glyphicon-search"></span>', false, [
                                    'onclick' => 'checkSpecDocument();',
                                    'title' => 'Перевірка за жетоном'
                                ]);
                            }
                            ?>
                        </div>
                    </div>
                    <div class="row>">
                        <div class="col-md-10" style="padding: 0;">
                            <?= $form->field($model, 'last_name')->textInput([
                                // 'onchange' => 'checkFIO();'
                            ]);?>
                            <?= $form->field($model, 'first_name')->textInput([
                                //  'onchange' => 'checkFIO();'
                            ]);?>
                            <?= $form->field($model, 'middle_name')->textInput([
                                //   'onchange' => 'checkFIO();'
                            ]); ?>
                        </div>
                        <div class="col-md-2">
                            <?php
                            //  if (!$update){
                            if (1 == 1){
                                echo Html::a('<span class="glyphicon glyphicon-search"></span>', false, [
                                    'onclick' => 'checkFIO();',
                                    'title' => 'Перевірка за П.І.Б.'
                                ]);;
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-6">
                    <?= $form->field($model, 'email'); ?>
                    <?= $form->field($model, 'phone'); ?>
                    <?= $form->field($model, 'direction', ['inputOptions' =>
                        ['class' => 'form-control', 'tabindex' => '4']])
                        ->dropDownList(\apiadmin\modules\adminx\models\UserData::$directionArray,
                            ['options' => [ $model->direction => ['Selected' => true]],]);?>
                    <?= $form->field($model, 'job_name')->textarea([
                        'rows' => '5'
                    ])->label('Місце роботи'); ?>
                </div>
            </div>
        </div>
    </div>

    <!--*************************************************************************** УПРАВЛЕНИЕ ПОДРАЗДЕЛЕНИЯМИ И РОЛЯМИ ПОЛЬЗОВАТЕЛЯ -->
    <div class="col-md-12 col-lg-6">
        <!--*************************************************************************** УПРАВЛЕНИЕ ПОДРАЗДЕЛЕНИЯМИ ПОЛЬЗОВАТЕЛЯ -->
        <div class="row">
            <div class="xCard ">

                <!--*************************************************************************** ПОДРАЗДЕЛЕНИЯ ПОЛЬЗОВАТЕЛЯ -->
                <div id="departmentsArea">
                    <b>Підрозділи користувача</b>
                    <div id="userDepartments">
                    </div>
                    <br>
                    <div id="addDepBtn" align="center">

                        <?= Html::button('Додати підрозділ', [
                            'class' => 'btn btn-primary',
                            'onclick' => '$("#selectArea").show();
                                      $("#addDepBtn").hide();
                                     // $("#departmentsArea").hide();
                                     // $("#selectPositionBlock").hide();
                                      '
                        ])?>
                    </div>

                </div>

                <!--***************************************************************** ВЫБОР НОВОГО ПОДРАЗДЕЛЕНИЯ ПОЛЬЗОВАТЕЛЯ -->
                <div id="selectArea" style="display: none;">
                    <!--***************************************************************** Дерево для выбора -->
                    <div class="row">
                        <div class="col-md-10">
                            <div id="selectTree" class="simpleBlock" style=" height: 50vh; width: auto; overflow: auto">
                                <?= \common\widgets\tree\TreeWidget::widget([
                                    'showFilter' => false,
                                    'useConserve' => false,
                                    'noShowPositions' => 1,
                                    'treeHeight' => '40vh'
                                ]);
                                ?>
                            </div>
                        </div>
                        <div class="col-md-1" align="center">
                            <?= Html::a('<span class="glyphicon glyphicon-plus"></span>', false,
                                [
                                    'onclick' => 'addUserDepartment();',
                                    'title' => 'Додати підрозділ',
                                ]);
                            ?>
                        </div>
                        <div class="col-md-1" align="center">
                            <?= Html::a('<span class="glyphicon glyphicon-remove"></span>', false,
                                [
                                    'onclick' => '$("#departmentsArea").show();
                                                      $("#selectArea").hide();
                                                      $("#addDepBtn").show();
                                                      ',
                                    'title' => 'Зховати список',

                                ]);
                            ?>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <!--*************************************************************************** УПРАВЛЕНИЕ РОЛЯМИ ПОЛЬЗОВАТЕЛЯ -->
        <div class="row">
            <div class="xCard">

                <!--*************************************************************************** РОЛИ ПОЛЬЗОВАТЕЛЯ -->
                <div id="RolesArea">
                    <b>Ролі користувача</b>
                    <div id="userRoles">
                    </div>
                    <br>
                    <div id="addRoleBtn" align="center">
                        <?= Html::button('Додати роль', [
                            'class' => 'btn btn-primary',
                            'onclick' => '$("#selectRoleArea").show();
                                                      $("#addRoleBtn").hide();
                                      '
                        ])?>
                    </div>

                </div>

                <!--***************************************************************** ВЫБОР НОВОЙ РОЛИ ПОЛЬЗОВАТЕЛЯ -->
                <div id="selectRoleArea" class="selectRoleArea" style="display: none;">
                    <!--***************************************************************** сЕЛЕКТ для выбора -->
                    <div class="row">
                        <div class="col-md-10">
                            <?php
                            echo Html::listBox('defaultRoles', null, $defaultRoles, [
                                'class' => 'form-control',
                            ]);

                            ?>
                        </div>
                        <div class="col-md-1" align="center">
                            <?= Html::a('<span class="glyphicon glyphicon-plus"></span>', false,
                                [
                                    'onclick' => 'addUserRole()',
                                    'title' => 'Додати роль',
                                ]);
                            ?>
                        </div>
                        <div class="col-md-1" align="center">
                            <?= Html::a('<span class="glyphicon glyphicon-remove"></span>', false,
                                [
                                    'onclick' => '$("#rolesArea").show();
                                                      $("#selectRoleArea").hide();
                                                      $("#addRoleBtn").show();
                                                      ',
                                    'title' => 'Зховати список',
                                ]);
                            ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--*************************************************************************** ОКНО ВЫБОРА НОВОГО ПОЛЬЗОВАТЕЛЯ -->
<div class="row xContent">
    <div class="col-md-12">
        <div class="userCardArea" id="selectPositionBlock" style="display: none;">
            <div class="row">
                <div class="col-md-11">
                    <select size="11" class="form-control " id="selectPosition"></select>
                </div>
                <div class="col-md-1" align="right">
                        <span>
                                               <?= Html::a('<span class="glyphicon glyphicon-plus"></span>', false,
                                                   [
                                                       'onclick' => 'choosePersona()',
                                                       'title' => 'Обрати',
                                                   ]);
                                               ?>
                            <b>   </b>
                            <?= Html::a('<span class="glyphicon glyphicon-remove"></span>', false,[
                                'onclick' => 'selectPositionBlockClose();',
                                'title' => 'Зховати список',
                            ]);
                            ?>

                        </span>
                </div>
            </div>
        </div>

    </div>
</div>

<!--*************************************************************************** КНОПКИ СОХРАНЕНИЯ -->
<div class="row xContent">
    <div class="col-md-12 col-lg-6">
        <div class="formButtons">
            <div class="row">
                <div class="col-md-12 col-lg-4">
                    <?= $form->field($model, 'username')->textInput(['disabled' => $update,]); ?>
                </div>
                <div class="col-md-12 col-lg-4">
                    <?php
                    if (!$update){
                        echo $form->field($model, 'password');;
                    }
                    ?>
                </div>
                <div class="col-md-12 col-lg-4">
                    <?php
                    if (!$update){
                        echo $form->field($model, 'retypePassword')->label('Підтвердження');;
                    }
                    ?>
                </div>

            </div>
            <div class="row">
                <div class="form-group" align="center">
                    <?= Html::button('Зберігти', [
                        'class' => 'btn btn-primary',
                        'name' => 'signup-button',
                        'onclick' => 'saveUser();'
                    ]) ?>
                    <?= Html::a('Відміна', '/adminxx/user',[
                        'class' => 'btn btn-danger', 'name' => 'reset-button'
                    ]);?>
                </div>
            </div>
        </div>
    </div>
</div>
<!--*************************************************************************** МУЛЬТИПОЛЕ ПОДРАЗДЕЛЕНИЙ -->
<div class="row xContent">
    <?= $form->field($model, 'multyFild')->textarea([
        'rows' => '5'
    ])->hiddenInput()->label(false);?>

</div>
<?php ActiveForm::end(); ?>










