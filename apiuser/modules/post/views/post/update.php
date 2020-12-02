<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use frontend\modules\post\assets\UpdadePostAsset;
use yii\jui\JuiAsset;


JuiAsset::register($this);
UpdadePostAsset::register($this);

$cleanImage = \Yii::getAlias('@web'). \Yii::$app->params['pathToFiles'] . '/image/clean.png';

$_id = (isset($model->id)) ? $model->id : 0;
$this->registerJs("
 var _id = {$_id};
 var _cleanImage = '{$cleanImage}';
 ",\yii\web\View::POS_HEAD);

$this->registerJs("
$( function() {
    $( '#tabs' ).tabs();
  } );
  ",\yii\web\View::POS_HEAD);


$this->title = \Yii::t('app', 'Пост');

?>

<div class="container-fluid">
    <div id="tabs">
        <ul>
            <li><a href="#tabs-1"><?=\Yii::t('app', 'Пост');?></a></li>
            <li><a href="#tabs-2"><?=\Yii::t('app', 'Медиа');?></a></li>
        </ul>
        <div id="tabs-1">
            <!--*************************************************************************** ИЗМЕНЕНИЕ ПОСТА  -->
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
            echo $form->field($model, 'content')->textarea(['col' => 40, 'row' => 3]);
            /*
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
            */

            echo $form->field($model, 'user_id')->hiddenInput()->label(false);
            echo Html::submitButton(\Yii::t('app', 'Сохранить'), ['class' => 'btn btn-primary', 'name' => 'submit-button']);
            echo Html::submitButton(\Yii::t('app', 'Отмена'), ['class' => 'btn btn-danger', 'name' => 'reset-button']);
            ActiveForm::end();
            ?>
        </div>
        <div id="tabs-2">
            <!--*************************************************************************** КРУД МЕДИА  -->
            <?php if (!$model->isNewRecord):?>
                <div id="listPostMediaArea">
                    <?php
                    echo Html::button(\Yii::t('app', 'Добавить изображение'), [
                        'class' => 'btn btn-primary newMediaBtn',
                        'data-type' => 'image',
                    ]);
                    echo Html::button(\Yii::t('app', 'Добавить ссылку'), [
                        'class' => 'btn btn-primary newMediaBtn',
                        'data-type' => 'link',
                    ])
                    ?>

                    <div id="listPostMedia">
                    </div>
                </div>
                <div id="newPostMedia" style="display: none" >
                    <H4 id="mediaTitle">fghfgh</H4>
                    <?php
                    $formImage = ActiveForm::begin([
                        'id' => 'post-media-create',
                        'options' => ['enctype' => 'multipart/form-data']
                    ]);
                    ?>
                    <?= $formImage->field($modelMedia, 'name');?>
                    <div id="imagePreview">
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
                            <?=$formImage->field($modelMedia, 'mediaFile')->fileInput()->label(false)?>
                        </div>
                    </div>
                    <?= $formImage->field($modelMedia, 'file_name');?>
                    <?= $formImage->field($modelMedia, 'type');?>
                    <?= $formImage->field($modelMedia, 'post_id')->hiddenInput()->label(false);?>
                    <?= Html::button(\Yii::t('app', 'Сохранить'), [
                        'class' => 'btn btn-primary saveMediaBtn',
                        'data-type' => 'image',
                    ]);?>
                    <?= Html::button(\Yii::t('app', 'Отмена'), ['class' => 'btn btn-danger', 'id' => 'resetMediaBtn']);?>

                    <?php ActiveForm::end();?>
                </div>

            <?php endif;?>
        </div>
    </div>
</div>

