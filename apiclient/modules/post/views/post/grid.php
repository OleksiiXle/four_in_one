<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Посты');
?>
<div class="row ">
    <div class="xHeader">
        <div class="col-md-6" align="left">
        </div>
        <div class="col-md-6" align="right" >
            <?php
            echo Html::a(Yii::t('app', 'Добавить новый'), Url::toRoute('/adminxx/translation/create'), [
                'class' =>'btn btn-primary',
            ]);
            ?>
        </div>
    </div>
</div>
<div class="row xContent">
    <div class="xCard">
        <?php
        echo $postProvider->drawGrid();
        ?>
    </div>
</div>
