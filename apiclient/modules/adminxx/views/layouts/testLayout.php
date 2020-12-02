<?php

use yii\helpers\Html;
use app\assets\AppAsset;
use \app\components\widgets\menuX\MenuXWidget;

//AppAsset::register($this);
\app\modules\adminxx\assets\AdminxxLayoutAsset::register($this);

?>
<?php
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => \yii\helpers\Url::to(['/images/np_logo.png'])]);?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
<?php $this->beginBody() ?>

<div class="container-fluid">
    <!--************************************************************************************************************* HEADER-->
    <div class="xHeader">
        <!--************************************************************************************************************* MENU BTN-->
        <div class="col-md-1">
            <button id="open-menu-btn" onclick="showMenu();">
                <span class="glyphicon glyphicon-list"></span>
            </button>
        </div>
        <!--************************************************************************************************************* CENTER-->

        <div class="col-md-10">
            <b>Center</b>
        </div>
        <!--************************************************************************************************************* LOGIN/LOGOUT-->
        <div class="col-md-1">
            <b>logout</b>
        </div>
    </div>
    <div class="col-md-12">
        <div class="xContent">
            <div class="container-fluid">
                <?= $content ?>
            </div>
        </div>
    </div>


</div>

<div id="xWrapper">
    <div class="xCover" ></div>
    <div class="xMenu" onclick="menuClick()">
        <div class="xMenuContent" ">
        <p>THIS IS CONTENT</p>
        <div class="menuTree">
            <?php
            if (1==0){
                echo \app\widgets\menuX\MenuXWidget::widget([
                    'model' => '',
                    'attribute' => 'kjgh',
                    'name' => '',
                ]) ;

            } else {
                echo MenuXWidget::widget([
                    'model' => '',
                    'attribute' => 'kjgh',
                    'name' => '',
                ]) ;
            }
            ?>
        </div>

    </div>
</div>

</div>


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
<script>
</script>


