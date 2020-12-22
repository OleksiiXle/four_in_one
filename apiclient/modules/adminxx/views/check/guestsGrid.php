<?php

use app\modules\adminxx\assets\AdminxxGuestsControlAsset;

AdminxxGuestsControlAsset::register($this);
$this->title = \Yii::t('app', 'Посетители');

?>

<div class="xContent">
    <div class="xCard">
        <?php
        echo $checkGrid->drawGrid();
        ?>
    </div>
</div>





