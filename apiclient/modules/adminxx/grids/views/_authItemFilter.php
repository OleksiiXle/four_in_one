<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\modules\adminxx\models\AuthItemX;

?>

<div class="container-fluid">
    <?php
    $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'post',
    ]);
    ?>
    <div class="xCard">
        <div class="row">
            <div class="col-md-12 col-lg-6 ">
                <div class="row">
                    <div class="col-md-12 col-lg-6">
                        <?php
                        echo $form->field($filter, 'type')
                            ->dropDownList(AuthItemX::getTypeDict(),
                                ['options' => [ $filter->type => ['Selected' => true]],]);
                        echo $form->field($filter, 'name');
                        echo $form->field($filter, 'rule_name')
                            ->dropDownList(AuthItemX::getRulesList(),
                                ['options' => [ $filter->rule_name => ['Selected' => true]],]);
                        ?>
                    </div>
                    <div class="col-md-12 col-lg-6">
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-6 ">
                <?php
                //todo***************** обязательные поля
                echo $form->field($filter, 'showOnlyChecked')->checkbox();
                echo $form->field($filter, 'allRowsAreChecked')->hiddenInput()->label(false);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="form-group" align="center" style="padding: 20px">
                <?php
                //  echo  Html::submitButton('Шукати', ['class' => 'btn btn-primary', 'id' => 'subBtn']);
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
