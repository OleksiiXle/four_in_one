<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use \app\modules\post\models\Post;

$this->title = \Yii::t('app', 'Новый пост');

?>

<div class="container-fluid">
    <div class="row">
        <div class="xCard">
            <?= Html::errorSummary($model)?>
            <?php
            $form = ActiveForm::begin([
                'id' => 'post-update-id',
                'options' => [
                    //  'enctype' => 'multipart/form-data',
                    'name' => 'post-update-form',
                ]
            ]);
            //   echo $form->field($model, 'targetFile');
            echo $form->field($model, 'name');
            echo $form->field($model, 'content')->textarea(['col' => 40, 'row' => 3]);
            echo Html::submitButton(\Yii::t('app', 'Сохранить'), ['class' => 'btn btn-primary', 'name' => 'submit-button']);
            echo Html::submitButton(\Yii::t('app', 'Отмена'), ['class' => 'btn btn-danger', 'name' => 'reset-button']);
            ActiveForm::end();
            ?>
        </div>
    </div>
    <div class="row">
        <div class="xCard">
            <?php
            echo var_dump($model->response);
            ?>
        </div>
    </div>
</div>

