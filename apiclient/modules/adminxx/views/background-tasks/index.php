<?php

use app\modules\adminxx\assets\AdminxxBackgroundTaskAsset;

AdminxxBackgroundTaskAsset::register($this);

$this->title =  'Фонові завдання';
?>

<div class="row ">
    <div class="xHeader">
    </div>
</div>
<div class="row xContent">
        <div class="xCard">
                <?php
                echo $grid->drawGrid();
                ?>
        </div>
</div>

<?php //***********************************  заготовки под модальные окна







