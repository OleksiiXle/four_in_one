<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\jui\JuiAsset;
use \yii\helpers\Url;

JuiAsset::register($this);
$this->title = 'Перевод';
$this->registerJs("
    var _dataForAutocompleteRu = {$dataForAutocompleteRu};
    var _dataForAutocompleteEn = {$dataForAutocompleteEn};
    var _dataForAutocompleteUk = {$dataForAutocompleteUk};
",\yii\web\View::POS_HEAD);

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
             echo $form->field($model, 'messageRU');
             echo $form->field($model, 'messageUK');
             echo $form->field($model, 'messageEN');
             echo $form->field($model, 'category')->hiddenInput()->label(false);
             echo $form->field($model, 'tkey')->hiddenInput()->label(false);
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

<script>
    /*
    var _dataForAutocompleteRu = {$dataForAutocompleteRu};
    var _dataForAutocompleteEn = {$dataForAutocompleteEn};
    var _dataForAutocompleteUk = {$dataForAutocompleteUk};

     */
    $(document).ready ( function(){
      //  console.log(_dataForAutocompleteRu);
      //  console.log(JSON.parse(_dataForAutocompleteRu));
        $( "#translation-messageru" ).autocomplete({
            source: _dataForAutocompleteRu,
            minLength: 3
        });
        $( "#translation-messageen" ).autocomplete({
            source: _dataForAutocompleteEn,
            minLength: 3
        });
        $( "#translation-messageuk" ).autocomplete({
            source: _dataForAutocompleteUk,
            minLength: 3
        });

    });

</script>
