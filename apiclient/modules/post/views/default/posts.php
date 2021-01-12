<?php
use yii\widgets\ListView;
?>

<div class="container">
    <?php
    echo ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '_post',
    ]);
    ?>
</div>
