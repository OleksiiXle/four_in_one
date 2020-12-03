<?php
use yii\helpers\Html;
use yii\helpers\Url;
use common\widgets\changeLanguage\ChangeLanguageWidget;
use common\assets\AppAsset;

AppAsset::register($this);

if (Yii::$app->session->getAllFlashes()){
         $fms = Yii::$app->session->getAllFlashes();
         $_fms = \yii\helpers\Json::htmlEncode($fms);
         $this->registerJs("var _fms = {$_fms};",\yii\web\View::POS_HEAD);
}
//$logoImg = Url::toRoute(['/images/sun_61831.png']);
$logoImg = Url::toRoute(['/images/np_logo.png']);

?>
<?php
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => $logoImg]);?>
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
<?php $this->beginBody() ;?>

<div id="mainContainer" class="container-fluid">
    <!--************************************************************************************************************* HEADER-->
    <div class="xLayoutHeader">

        <!--************************************************************************************************************* MENU BTN-->
        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2" align="left" style="padding-left: 2px; padding-right: 0">
        </div>
          <!--************************************************************************************************************* CENTER-->
        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 " >
        </div>
        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 " >
            <?php
            echo ChangeLanguageWidget::widget();
            ?>
        </div>
        <!--************************************************************************************************************* LOGIN/LOGOUT-->
        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2" align="center" style="padding-left: 1px">
        </div>
    </div>
    <div class="xLayoutContent">

        <div id="flashMessage" style="display: none">
        </div>

        <?= $content ?>
    </div>

</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>


