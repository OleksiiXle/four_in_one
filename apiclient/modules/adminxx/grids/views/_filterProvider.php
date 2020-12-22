<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\models\UserData;
?>
<div class="user-search container-fluid" >
    <?php
    $form = ActiveForm::begin([
            'options' => [
                    'id' => 'providerFilterForm'
            ]
    ]);
    ?>
    <div class="xCard">
        <div class="row">
            <div class="col-md-12 col-lg-6 ">
                <div class="xCard">
                    <?php
                    echo $form->field($filter, 'client_id');
                    echo $form->field($filter, 'name');
                    ?>
                </div>
            </div>
            <div class="col-md-12 col-lg-6 ">
            </div>
        </div>
        <div class="row">
            <div class="form-group" align="center" style="padding: 20px">
                <?php
                //  echo  Html::submitButton('Шукати', ['class' => 'btn btn-primary', 'id' => 'subBtn']);
                echo  Html::submitButton(Yii::t('app', 'Применить фильтр'), [
                    'class' => 'btn btn-primary',
                    'id' => 'subBtn',
                  //  'onclick' => 'useFilter();'
                ]);
                ?>
                <?= Html::button(Yii::t('app', 'Очистить фильтр'), [
                    'class' => 'btn btn-danger',
                    'id' => 'cleanBtn',
                    'onclick' => 'cleanOauthFilter();',
                ]) ?>
                <?php
                echo Html::a( Yii::t('app', 'Добавить новый'), Url::toRoute('/adminxx/provider/create-provider'), [
                    'class' =>'btn btn-success',
                ]);

                ?>
            </div>
        </div>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<script>
    function cleanOauthFilter() {
        cleanFilter(false);
        $("#providerFilterForm").submit();
    }
</script>


