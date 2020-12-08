<?php
use yii\helpers\Html;
use apiadmin\modules\adminxx\models\UserM;
use common\widgets\xlegrid\Xlegrid;
use common\widgets\menuAction\MenuActionWidget;
use yii\helpers\Url;
use apiadmin\modules\adminxx\assets\AdminxxUserAsset;

AdminxxUserAsset::register($this);

$this->title = Yii::t('app', 'Пользователи');

?>
<style>
    .usersGrid{
        padding: 5px;
    }
</style>

<div class="row ">
    <div class="xHeader">
        <div class="col-md-6" align="left">
        </div>
        <div class="col-md-6" align="right" >
            <?php
            echo Html::a( 'Рєєстрація нового користувача', \yii\helpers\Url::toRoute('/adminxx/user/signup-by-admin'), [
                'class' =>'btn btn-primary',
            ]);
            ?>
        </div>
    </div>
</div>
<div class="row xContent">
    <div class="usersGrid xCard">
        <?php
        //Pjax::begin(['id' => 'users-grid-container1',]);
        ?>
        <div id="users-grid-container" >
            <?php
            echo $usersGrid->drawGrid();
            ?>
            <?php //Pjax::end() ?>

        </div>

    </div>
</div>
