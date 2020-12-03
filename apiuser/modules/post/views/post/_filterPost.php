<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>


<div class="container-fluid">
    <?php
    $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'post',
        'id' => 'postFilterForm',
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
                        echo $form->field($filter, 'type');
                        echo $form->field($filter, 'name');
                        ?>
                    </div>
                    <div class="col-md-12 col-lg-6">
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
                <?=Html::a(Yii::t('app', 'В файл'), \yii\helpers\Url::toRoute('/adminxx/translation/upload'), [
                    'class' => 'btn btn-success no-pjax',
                ])?>
            </div>
        </div>

    </div>
    <?php ActiveForm::end(); ?>
</div>

<script>
    $(document).ready(function(){
    });
</script>



