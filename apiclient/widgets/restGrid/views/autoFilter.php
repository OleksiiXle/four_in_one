<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
$tmp =1;
$colsCount = $model->colsCount;

switch ($colsCount) {
    case 1:
        $colClass = 'col-md-12 col-lg-12';
        break;
    case 2:
        $colClass = 'col-md-6 col-lg-6';
        break;
    case 3:
        $colClass = 'col-md-4 col-lg-4';
        break;
    case 4:
        $colClass = 'col-md-3 col-lg-3';
        break;
}





?>

<div class="container-fluid">
    <?php
    $form = ActiveForm::begin([
       // 'action' => ['index'],
        'method' => 'post',
        'id' => 'autoFilterForm',
    ]);
    ?>
    <div class="xCard">
        <div class="row">
            <div class="col-md-12 col-lg-12 ">
                <div class="row">
                <?php for ($i = 1; $i <= $colsCount; $i++) :?>
                        <div class="<?=$colClass?>">
                            <?php
                            foreach ($attributes as $name => $properties) {
                                if ($properties['col'] == $i) {
                                    switch ($properties['renderType']) {
                                        case 'input':
                                            echo $form->field($model, $name);
                                            break;
                                        case 'dropdownList':
                                            echo $form->field($model, $name, ['inputOptions' =>
                                                ['class' => 'form-control']])
                                                ->dropDownList($properties['list'],
                                                    ['options' => [ $model->{$name} => ['Selected' => true]],]);

                                            break;
                                    }
                                }
                            }
                            ?>
                        </div>
                <?php endfor;?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group" align="center" style="padding: 20px">
                <?php
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
