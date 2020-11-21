<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\MenuXX;

?>
<?//**********************************************************************************************?>
<?php $form = ActiveForm::begin(['id' => 'menuMmodifyForm']); ?>
            <?= $form->field($model, 'node1')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'nodeAction')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'menu_id')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'id')->hiddenInput()->label(false); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-11">
            <?php
            echo $form->field($model, 'sort', ['inputOptions' => ['class' => 'form-control', 'tabindex' => '1']]);
            echo $form->field($model, 'name', ['inputOptions' => ['class' => 'form-control', 'tabindex' => '1']]);
            echo $form->field($model, 'route', ['inputOptions' => ['class' => 'form-control', 'tabindex' => '3']])
                ->dropDownList($routes, ['options' => [ $model->route => ['Selected' => true]],]);
            echo $form->field($model, 'role')
                ->dropDownList($permissions,
                    ['options' => [ $model->role => ['Selected' => true]],]);
            echo $form->field($model, 'access_level')
                ->dropDownList(MenuXX::ACCESS_LEVEL_DICT,
                    ['options' => [ $model->access_level => ['Selected' => true]],]);
            ?>
        </div>
    </div>
    <div class="row" align="center">
        <div class="col-md-11">
            <?= Html::button($model->isNewRecord ? \Yii::t('app', 'Создать')
                : \Yii::t('app', 'Сохранить'),
                [
                    'id' => 'btn_' . $model->menu_id . '_updateForm',
                    'class' => 'btn btn-primary',
                ]); ?>
            <?= Html::button( \Yii::t('app', 'Отмена'),
                ['class' =>  'btn btn-danger',
                    // 'onclick' => '$("#main-modal-md").modal("hide");'
                    'onclick' => 'hideModal();',
/*
                    'onclick' => '
                        $("#modal-content").html("");
                        $("#main-modal-md").modal("hide");
                    ;'
                    */
                ]) ?>
        </div>
    </div>

</div>
 <?php ActiveForm::end(); ?>
