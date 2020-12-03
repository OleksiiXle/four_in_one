<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\components\models\Translation;
use \yii\helpers\Url;

$this->title = 'Перевод';

?>

<div class="container-fluid">
    <h3><?= Html::encode($this->title ) ?></h3>
    <div class="row">
        <div class="col-md-12">
            <?php $form = ActiveForm::begin([
                'layout'=>'horizontal',
            ]); ?>
            <?= Html::errorSummary($model)?>
            <?php
            echo $form->field($model, 'category', ['inputOptions' =>
                ['class' => 'form-control','tabindex' => '1']])
                ->dropDownList(Translation::LIST_CATEGORY,
                    ['options' => [ $model->language => ['Selected' => true], ],]);
             echo $form->field($model, 'messageRU');
             echo $form->field($model, 'messageUK');
             echo $form->field($model, 'messageEN');
             echo $form->field($model, 'tkey')->hiddenInput()->label(false);
             echo $form->field($model, 'id')->hiddenInput()->label(false);

            ?>
            <div class="form-group" align="center">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                <?= Html::a('Отмена', Url::toRoute('/adminxx/translation'),[
                    'class' => 'btn btn-danger', 'name' => 'reset-button'
                ]);?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
