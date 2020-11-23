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
    <br>
    <br>
    <div class= "col-md-12 " align="center">
        <div class="row">
            <br>
            <br>
            <br>
            <div class="alert alert-danger">
                <h3><?= nl2br(Html::encode($message)) ?></h3>
            </div>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>

        </div>
    </div>

</div>
