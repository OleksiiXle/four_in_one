<?php
$file =  \Yii::getAlias('@web') . '/images/VID.mp4';
?>
<video class="responsive-video" controls>
    <source src="<?=$file?>" type="video/mp4">
</video>