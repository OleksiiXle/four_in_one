<?php
use yii\helpers\Html;

$this->title = Yii::t('app', 'АПИ клиенты');
?>
<div class="row ">
    <div class="xHeader">
        <div class="col-md-6" align="left">
        </div>
        <div class="col-md-6" align="right" >
            <?php
            echo Html::a('Добавить новый', \yii\helpers\Url::toRoute('/adminxx/provider/create'), [
                'class' =>'btn btn-primary',
            ]);
            ?>
        </div>
    </div>
</div>
<div class="row xContent">
    <div class="providerGrid xCard">
        <div id="providers-grid-container" >
            <?php
            echo $dataProvider->drawGrid();
            ?>
        </div>
    </div>
</div>
