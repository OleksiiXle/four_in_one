<?php
use yii\helpers\Html;
use yii\helpers\Url;
use apiadmin\modules\adminxx\models\AuthItemX;

$this->title =  Yii::t('app', 'Роли, разрешения');
?>
<div class="row ">
    <div class="xHeader">
        <div class="col-md-6" align="left">
        </div>
        <div class="col-md-6" align="right" >
            <?php
            echo Html::a(Yii::t('app', 'Создать роль'), Url::toRoute(['/adminxx/auth-item/create', 'type' => AuthItemX::TYPE_ROLE]),
                [
                    'class' =>'btn btn-primary',
                ]);
            echo '  ';
            echo Html::a(Yii::t('app', 'Создать разрешение'), Url::toRoute(['/adminxx/auth-item/create', 'type' => AuthItemX::TYPE_PERMISSION]),  [
                'class' =>'btn btn-primary',
            ]);
            echo '  ';
            ?>
        </div>
    </div>

</div>
<div class="row xContent">
    <div class="xCard">
        <?php
        echo $authItemGrid->drawGrid();
        ?>
    </div>
</div>

