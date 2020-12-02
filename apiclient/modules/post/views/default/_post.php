<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

?>

<div class="card">
    <div class="row">
        <div class="col-md-6">
            <div class="card-image">
                <img src="<?=$model->mainImage?>">
            </div>
        </div>
        <div class="col-md-6" align="center">
            <h4><?=Html::encode($model->name)?></h4>
        </div>
    </div>
    <div class="row">
        <div class="card-content">
            <p><?= HtmlPurifier::process($model->content) ?></p>
        </div>
    </div>
</div>
