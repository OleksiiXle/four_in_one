<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<?//**********************************************************************************************?>
<?php $form = ActiveForm::begin(['id' => 'tree-modify-form']); ?>
            <?= $form->field($model, 'node1')->hiddenInput(['value' => $id])->label(false) ?>
            <?= $form->field($model, 'nodeAction')->hiddenInput(['value' => $mode])->label(false) ?>

<div class="row">
    <div class="col-md-12">
        <?php
        echo $form->field($model, 'id')->hiddenInput(['disabled' => true])->label(false);
        echo $form->field($model, 'name')->textInput(['disabled' => true]);
        ?>
    </div>
    <div class="col-md-6">
        <?php
        /*
        echo $form->field($model, 'staff_unit')
            ->dropDownList(Dictionary::$dictYes,
                ['options' => [ $model->staff_unit => ['Selected' => true]],])
            ->label('Штатний підрозділ');
        echo $form->field($model, 'ab_subdiv_type')
            ->dropDownList(Dictionary::getDictionaryListMnemo('dep_type_name'),
                ['options' => [ $model->ab_subdiv_type => ['Selected' => true]],])
            ->label('Tипове найменування') ;
        echo $form->field($model, 'status')
            ->dropDownList(Dictionary::getDictionaryList('dep_status'),
                ['options' => [ $model->status => ['Selected' => true]],])->label('Статус') ;
        echo $form->field($model, 'unit_block')
            ->dropDownList(Dictionary::getDictionaryList('dep_block'),
                ['options' => [ $model->unit_block => ['Selected' => true]],])->label('Блок підрозділів');
        echo $form->field($model, 'c_department_code')
            ->dropDownList(Dictionary::getDictionaryListMnemo('dep_main_work_direction'),
                ['options' => [ $model->c_department_code => ['Selected' => true]],])
            ->label('Головний напрямок діяльності') ;
        echo $form->field($model, 'direction1')
            ->dropDownList(Dictionary::getDictionaryList('dep_main_sub_direction'),
                ['options' => [ $model->direction1 => ['Selected' => true]],])
            ->label('Основний напрямок діяльності') ;
        echo $form->field($model, 'direction2')
            ->dropDownList(Dictionary::getDictionaryList('dep_main_sub_direction'),
                ['options' => [ $model->direction2 => ['Selected' => true]],])
            ->label('Напрямок діяльності') ;
        echo $form->field($model, 'direction3')
            ->dropDownList(Dictionary::getDictionaryList('dep_func_sub_direction'),
                ['options' => [ $model->direction3 => ['Selected' => true]],])
            ->label('Функціональний напрямок') ;
        echo $form->field($model, 'activity_region')
            ->dropDownList(Dictionary::getDictionaryList('department_activity_region'),
                ['options' => [ $model->activity_region => ['Selected' => true]],])
            ->label('Регіон обслуговування') ;
        */
        ?>
    </div>
</div>

        <div class="row" align="center">
            <?= Html::button('Видалити', ['id' => 'tree-modify', 'class' => 'btn btn-primary',
                'onclick' =>
                    'treeModify("menux");
                    $("#main-modal-lg").modal("hide");'
            ]); ?>
            <?= Html::button( 'Відміна',
                ['class' =>  'btn btn-success',
                    'onclick' => '$("#main-modal-lg").modal("hide");'
                ]) ?>

        </div>
 <?php ActiveForm::end(); ?>
