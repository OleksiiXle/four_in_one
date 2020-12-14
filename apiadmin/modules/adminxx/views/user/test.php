<?php
use yii\helpers\Html;
use apiadmin\modules\adminxx\assets\AdminxxUserAsset;
use \yii\helpers\Url;

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
            echo Html::a( Yii::t('app', 'Регистрация нового пользователя'), Url::toRoute('/adminxx/user/signup-by-admin'), [
                'class' =>'btn btn-primary',
            ]);
            ?>
        </div>
    </div>
</div>
<div class="row xContent">
    <div class="usersGrid xCard">
        <div id="users-grid-container" >
            <?php
            echo $usersGrid->drawGrid();
            ?>
        </div>
    </div>
</div>
