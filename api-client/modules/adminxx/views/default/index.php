<style>
    .adminPanelBtn{
        height: 15%;
        width: 30%;
        margin: 15px;
        padding: 40px;
        font-size: larger;
        color: black;
        box-shadow: 0 4px 5px 0 rgba(0, 0, 0, 0.14), 0 1px 10px 0 rgba(0, 0, 0, 0.12), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
        background-color: lightgrey;

    }
</style>
<?php
$this->title =  Yii::t('app', 'Администрирование');
?>
<div class="row xContent"  style="height: 90%">
    <?php foreach ($buttons as $button):?>
        <?php if ($button['show']):?>
            <?php
                echo \yii\helpers\Html::a($button['name'], $button['route'],[
                        'class' => 'btn  adminPanelBtn',
                ])
            ?>
        <?php endif;?>
    <?php endforeach;?>

</div>

