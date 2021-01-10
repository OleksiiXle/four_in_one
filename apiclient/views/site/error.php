<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

//$this->title = $name;
?>

<div class="site-error">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class= "col-md-12 "">
        <div class="row">
            <div class="alert alert-danger">
                <h3><?= nl2br(Html::encode($message)) ?></h3>
            </div>
        </div>
    </div>

</div>
