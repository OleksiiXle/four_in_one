<?php
use yii\widgets\ListView;
use app\modules\post\assets\PostsListAsset;

PostsListAsset::register($this);
?>

<div class="container">
    <?php
    echo ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '_post',
    ]);
    ?>
</div>
