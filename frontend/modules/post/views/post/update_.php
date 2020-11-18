<?php
use yii\helpers\Html;
//use \yii\widgets\ActiveForm;
//use yii\bootstrap\ActiveForm;
use \macgyer\yii2materializecss\widgets\form\ActiveForm;


\frontend\modules\post\assets\UpdadePostAsset::register($this);

$cleanImage = \Yii::getAlias('@web'). \Yii::$app->params['pathToFiles'] . '/image/clean.png';

$this->registerJs("
 var _id = {$model->id};
 var _cleanImage = '{$cleanImage}';
 ",\yii\web\View::POS_HEAD);


$this->title = \Yii::t('app', 'Пост');
?>

<div class="container-fluid">

    <h3><?= Html::encode($this->title) ?></h3>
    <div class="row">
        <div class="col-md-6">
            <?= Html::errorSummary($model)?>
            <?php
            $form = ActiveForm::begin([
                'id' => 'post-update-id',
                'options' => [
                        'enctype' => 'multipart/form-data',
                    'name' => 'post-update-form',

                ]
            ]);
         //   echo $form->field($model, 'targetFile');
            echo $form->field($model, 'name');
            echo $form->field($model, 'type')->dropDownList(\frontend\modules\post\models\Post::getListType(),
                ['options' => [ $model->type => ['Selected' => true]]]);
            echo $form->field($model,'content')
                ->widget(dosamigos\ckeditor\CKEditor::class,
                    [
                        // 'preset'=>YII_ENV_DEV?'standard':'basic',
                        'preset'=>'basic',
                        'options' => [],
                        'clientOptions'=>[
                            'allowedContent' => true,
                            // 'width' => "300px",
                        ]])->label(false);
            echo $form->field($model, 'user_id')->hiddenInput()->label(false);
            echo Html::submitButton(\Yii::t('app', 'Сохранить'), ['class' => 'btn btn-primary', 'name' => 'signup-button']);
            echo Html::submitButton(\Yii::t('app', 'Отмена'), ['class' => 'btn btn-danger', 'name' => 'reset-button']);
            ActiveForm::end();
            ?>
        </div>
        <div class="col-md-6" style="background-color: aliceblue">
            <!--*************************************************************************** ПРАВАЯ ПОЛОВИНА  -->
            <?php if (!$model->isNewRecord):?>
                <div id="listPostMediaArea">
                    <?php
                    echo Html::button(\Yii::t('app', 'Добавить изображение'), [
                        'class' => 'btn btn-primary',
                        'id' => 'newMediaBtn',
                    ])
                    ?>

                    <div id="listPostMedia">
                    </div>
                </div>
                <div id="newPostMedia" style="display: none" >
                    <?php
                    $formMedia = ActiveForm::begin([
                        'id' => 'post-media-create',
                        'options' => ['enctype' => 'multipart/form-data']
                    ]);
                    echo $formMedia->field($modelMedia, 'name');
                    ?>
                    <div align="center">
                        <?= Html::img($cleanImage, [
                            'id' => 'previewImage',
                            'alt'=> '?',
                            'class' => '',
                            'height' => '200px',
                            'width' => 'auto',
                            'hspace' => '20px',
                            'vspace' => '20px',
                            'align' => 'center',

                        ])?>
                        <?=$formMedia->field($modelMedia, 'mediaFile')->fileInput()->label(false)?>
                    </div>
                    <?php
                    echo $formMedia->field($modelMedia, 'file_name')->hiddenInput()->label(false);
                    echo $formMedia->field($modelMedia, 'post_id')->hiddenInput()->label(false);
                    echo $formMedia->field($modelMedia, 'type')->hiddenInput()->label(false);
                    echo Html::button(\Yii::t('app', 'Сохранить'), ['class' => 'btn btn-primary', 'id' => 'saveMediaBtn']);
                    echo Html::button(\Yii::t('app', 'Отмена'), ['class' => 'btn btn-danger', 'id' => 'resetMediaBtn']);
                    ActiveForm::end();
                    ?>
                </div>

            <?php endif;?>
        </div>
    </div>
</div>

