<?php

use yii\helpers\Html;
use yii\helpers\Url;
use common\components\widgets\menuX\MenuXWidget;
use frontend\assets\XLayoutAsset;
use yii\bootstrap\Alert;

XLayoutAsset::register($this);
if (Yii::$app->session->getAllFlashes()){
         $fms = Yii::$app->session->getAllFlashes();
         $_fms = \yii\helpers\Json::htmlEncode($fms);
         $this->registerJs("var _fms = {$_fms};",\yii\web\View::POS_HEAD);
}

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
<?php $this->beginBody() ;?>

<div id="mainContainer" class="container-fluid">
    <!--************************************************************************************************************* HEADER-->
    <div class="xLayoutHeader">

        <!--************************************************************************************************************* MENU BTN-->
        <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2" align="left" style="padding-left: 2px; padding-right: 0">
            <!--  <button id="open-menu-btn" onclick="showModal(500,600, 'lokoko the best');" class="xMenuBtn" >-->
            <button id="open-menu-btn" onclick="showMenu();" class="xMenuBtn" >
                  <span class="glyphicon glyphicon-list" ></span>
              </button>
          </div>
          <!--************************************************************************************************************* CENTER-->

        <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9 " >
            <h3 style="margin-top: 15px;margin-bottom: 15px; white-space: nowrap; overflow: hidden;"><?= Html::encode($this->title) ?></h3>
        </div>
        <!--************************************************************************************************************* LOGIN/LOGOUT-->
        <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1" align="center" style="padding-left: 1px">
            <?php
            $r=1;
            if (!Yii::$app->user->isGuest){
                echo Html::beginForm(['/site/logout'], 'post');
                echo Html::submitButton(
                    'Logout',
                    ['class' => 'btn btn-link ']
                );
                echo Html::endForm();
            } else {
                echo Html::a('<span class="glyphicon glyphicon-log-in" ></span>', '/site/login',
                    ['title' => 'Войти']);
                /*
                            <a href="/adminx" title="На гоговну сторінку">
                <span class="glyphicon glyphicon-home" ></span>
            </a>

                 */
            }
            ?>

        </div>
    </div>
    <div class="xLayoutContent">
        <?php
        /*
        foreach (Yii::$app->session->getAllFlashes() as $key => $message) {
            if(Yii::$app->getSession()->hasFlash($key)){
                echo Alert::widget([
                    'options' => [
                        'class' => (in_array($key, ['success', 'info', 'warning', 'danger']) ? 'alert-' . $key : 'alert-info'),
                    ],
                    'body' => $message,
                ]);
            }


        }
        */
        ?>
        <div id="flashMessage" class="alert" style="display: none">
        </div>

        <?= $content ?>
        <div class="xFooter">
            <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                <p>oleksii.xle69@gmail.com</p>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                <div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                &copy; Oleksii Khlieskkov <?= date('Y') ?>
            </div>
        </div>
    </div>

</div>


<div id="xWrapper">
    <div id="xCover" ></div>
    <div id="xMenu" onclick="menuClick()">
        <div id="xMenuContent" >
        <button class="xMenuCloseBtn" onclick="hideMenu();">
            <span class ="img-rounded">
                <img  src="<?=\yii\helpers\Url::to('@web/images/np_logo.png');?>" height="50px" width="50px;">
            </span>

        </button>
        <div class="menuTree">
            <?php
            echo MenuXWidget::widget([
                'showLevel' => '1',
                'accessLevels' => [0,2]
            ]) ;
            ?>
        </div>

    </div>
    </div>
    <div id="xModal">
        <div id="xModalWindow">
            <table class="table xModalHeader">
                <tr>
                    <td>
                      <span id="xModalHeader"></span>

                    </td>
                    <td align="right">
                        <button id="xModalCloseBtn" onclick="hideModal();">
                            <span class="glyphicon glyphicon-remove-circle" ></span>
                        </button>
                    </td>
                </tr>
            </table>
            <div id="xModalContent">
                <b>lokoko</b>
            </div>
        </div>
    </div>


</div>


<div id="preloaderCommonLayout" style="display: none">
    <div class="page-loader-circle"></div>
    <div id="preloaderText"></div>
</div>


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
<script>
</script>


