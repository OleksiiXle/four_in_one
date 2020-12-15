<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use \apiadmin\modules\adminxx\models\AuthItemX;
use yii\helpers\Url;

switch ($model->type){
    case AuthItemX::TYPE_ROLE:
        $this->title = \Yii::t('app', 'Роль');
        break;
    case AuthItemX::TYPE_PERMISSION:
        $this->title = \Yii::t('app', 'Разрешение');
        break;
    case AuthItemX::TYPE_ROUTE:
        $this->title = \Yii::t('app', 'Маршрут');
        break;

}
$this->title .= ' ' . $model->name;


$_assigments = \yii\helpers\Json::htmlEncode($assigments);
$this->registerJs("
    var _assigments = {$_assigments};
    var _name       = '{$model->name}';
    var _type       = '{$model->type}';
",\yii\web\View::POS_HEAD);
\apiadmin\modules\adminxx\assets\AdminxxUpdateAuthItemAsset::register($this);

$showSelects = (substr($model->name, 0,1) == '/') ? 'style= display:none;' : '';


?>
<div class="container-fluid">
    <div class="row">
        <?php
        $form = ActiveForm::begin([
            'id' => 'form-update',
        ]);
        echo $form->field($model, 'type')->hiddenInput()->label(false);
        ?>
        <div class="col-md-3">
            <h3><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="col-md-3">
            <?php
            echo $form->field($model, 'description');
            ?>
        </div>
        <div class="col-md-3">
            <?php
            echo $form->field($model, 'rule_name')
                ->dropDownList(\apiadmin\modules\adminxx\models\AuthItemX::getRulesList(),
                    ['options' => [ $model->rule_name => ['Selected' => true]],]);
            ?>
        </div>
        <div class="col-md-3">
            <div class="form-group" align="center">
                <?= Html::submitButton( \Yii::t('app', 'Сохранить'), ['class' => 'btn btn-primary', 'name' => 'update-button']) ?>
                <?= Html::a(\Yii::t('app', 'Отмена'), Url::toRoute('/adminxx/auth-item'),[
                    'class' => 'btn btn-success', 'name' => 'reset-button'
                ]);?>
                <?= Html::submitButton(\Yii::t('app', 'Удалить'), [
                    'class' => 'btn btn-danger',
                    'name' => 'delete-button',
                    'value' => 'delete',
                    'data' => ['confirm' => \Yii::t('app', 'Удалить')]
                ]) ?>

            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="row">
        <div class="col-md-12">
                <h4><b><?=Yii::t('app', 'Разрешения')?></b></h4>
                <div id="authItems" <?=$showSelects;?>>
                    <div class="col-md-5 userSelect">
                        <h5><?=\Yii::t('app', 'Доступные');?></h5>
                        <select multiple size="40" class="form-control list" data-target="avaliable"></select>
                    </div>
                    <div class="col-md-2 userSelect" align="center">
                        <br><br>
                        <?= Html::a('&gt;&gt;' , false, [
                            'class' => 'btn btn-success btn-assign actionAssign',
                            'data-rout' => Url::toRoute('/adminxx/auth-item/assign'),
                            'data-name' => $model->name,
                            'data-target' => 'avaliable',
                            'title' => Yii::t('app', 'Добавить')
                        ]) ?><br><br>
                        <?= Html::a('&lt;&lt;', false, [
                            'class' => 'btn btn-danger btn-assign actionRevoke',
                            'data-rout' => Url::toRoute('/adminxx/auth-item/revoke'),
                            'data-name' => $model->name,
                            'data-target' => 'assigned',
                            'title' => Yii::t('app', 'Удалить')
                        ]) ?>
                    </div>
                    <div class="col-md-5 userSelect">
                        <h5><b><?=\Yii::t('app', 'Назначенные');?></b></h5>
                        <select multiple size="40" class="form-control list" data-target="assigned"></select>
                    </div>
                </div>

        </div>
    </div>
</div>

