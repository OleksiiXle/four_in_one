<?php
use yii\helpers\Html;

$this->title = 'После авторизации через сторонний сервис'
?>

<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <div class="xCard">
                <div><h3><?= Html::encode('Сохраненные данные для последующей работы с АПИ:') ?></h3></div>
                <div>
                    <?php
                    echo var_dump(Yii::$app->user->apiLoginInfo);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
