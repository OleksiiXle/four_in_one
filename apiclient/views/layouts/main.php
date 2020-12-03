<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

$absoluteBaseUrl = Url::base(true);
$this->registerJs("
    const _BASE_URL = '{$absoluteBaseUrl}';
",\yii\web\View::POS_HEAD);

AppAsset::register($this);

//$logoImg = Url::toRoute(['/images/sun_61831.png']);
$logoImg = Url::toRoute(['/images/np_logo.png']);
$exitLogo = Url::toRoute('/images/log_logout_door_1563.png');

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => 'Home', 'url' => ['/site/index']],
            Yii::$app->user->isGuest ? (
            ['label' => 'Login', 'url' => ['/site/login']]
            ) : (
                '<li>'
                . Html::beginForm(['/site/logout-from-api'], 'post')
                . Html::submitButton(
                    'Logout from API (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>'
                . '<li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    'Logout(' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>'
            ),
            Yii::$app->user->isGuest ? (
            ['label' => 'Signup', 'url' => ['/site/signup']]
            ) : '',
            Yii::$app->user->isGuest ? (
            ['label' => 'Signup to api', 'url' => \yii\helpers\Url::toRoute(['/site/login', 'mode' => 'withSignup'])]
            ) : ''


        ],
    ]);

    NavBar::end();
    ?>
    <?php
    /*
    if (!Yii::$app->user->isGuest){
        $icon = \yii\helpers\Url::toRoute('@web/images/log_logout_door_1563.png');
        echo Html::beginForm(['/site/logout'], 'post');
        echo Html::submitButton(
            '<span> <img  src="' . $icon . '" height="30px" width="30px;">' . Yii::$app->user->getIdentity()->username .  '</span>',
            ['class' => 'btn btn-link ']
        );
        echo Html::endForm();
    }
    */
    ?>

    <div class="container">
        <?= $content ?>
    </div>

</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; My Company <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
