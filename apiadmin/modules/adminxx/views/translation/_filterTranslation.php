<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\jui\JuiAsset;

JuiAsset::register($this);
$autocompleteArray = $filter->dataForAutocomplete;
$dataForAutocompleteRu =  $autocompleteArray['ru-RU'];
$dataForAutocompleteUk =  $autocompleteArray['uk-UK'];
$dataForAutocompleteEn =  $autocompleteArray['en-US'];
$this->registerJs("
    var _dataForAutocompleteRu = {$dataForAutocompleteRu};
    var _dataForAutocompleteEn = {$dataForAutocompleteEn};
    var _dataForAutocompleteUk = {$dataForAutocompleteUk};
",\yii\web\View::POS_HEAD);

$_exportQuery = \yii\helpers\Json::htmlEncode($exportQuery);
$this->registerJs("
    var _exportQuery      = {$_exportQuery};
",\yii\web\View::POS_HEAD);

?>


<div class="container-fluid">
    <?php
    $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'post',
        'id' => 'userFilterForm',
    ]);
    ?>
    <div class="xCard">

        <div class="row">
            <div class="col-md-12 col-lg-6 ">
                <div class="row">
                    <div class="col-md-12 col-lg-6">
                        <?php
                        echo $form->field($filter, 'checkedIdsJSON')->textarea([
                                'cols' => 30, 'rows' => '3',
                        ])->hiddenInput()->label(false);
                        echo $form->field($filter, 'messageRU');
                        echo $form->field($filter, 'messageUK');
                        echo $form->field($filter, 'messageEN');
                        ?>
                    </div>
                    <div class="col-md-12 col-lg-6">
                        <?php
                        ?>
                        <div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-6 ">
                <?php
                echo $form->field($filter, 'showOnlyChecked')->checkbox([
                      //  'onchange' => 'checkOnlyChecked(this);'
                ]);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="form-group" align="center" style="padding: 20px">
                <?php
              //  echo  Html::submitButton('Шукати', ['class' => 'btn btn-primary', 'id' => 'subBtn']);
                echo  Html::button(Yii::t('app', 'Поиск'), [
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
                <?=Html::a(Yii::t('app', 'В файл'), '/adminxx/translation/upload', [
                    'class' => 'btn btn-success no-pjax',
                ])?>
            </div>
        </div>

    </div>
    <?php ActiveForm::end(); ?>
</div>

<script>
$(document).ready(function(){
    $( "#translationfilter-messageen" ).autocomplete({
        source: _dataForAutocompleteEn,
        minLength: 3
    });
    $( "#translationfilter-messageru" ).autocomplete({
        source: _dataForAutocompleteRu,
        minLength: 3
    });
    $( "#translationfilter-messageuk" ).autocomplete({
        source: _dataForAutocompleteUk,
        minLength: 3
    });

});
</script>


