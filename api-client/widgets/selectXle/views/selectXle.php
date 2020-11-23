
<?php if (!empty($userStyles)):?>
    <style>
        .<?='listItem_' . $selectId?>{
            <?php foreach ($userStyles['listItem'] as $key => $value):?>
                <?=$key . ': ' . $value . '!important;'?>
            <?php endforeach;?>
        }
        .<?='itemsArea_' . $selectId?>{
            <?php foreach ($userStyles['itemsArea'] as $key => $value):?>
                <?=$key . ': ' . $value . '!important;'?>
            <?php endforeach;?>
        }
    </style>
<?php endif;?>


<?php
$this->registerJs("jQuery('#$selectId').selectXle('$selectId', '$selectedItem', '$listData[$selectedItem]', '$jsFunctionBody');");

?>
<div id="<?=$selectId?>" style="position: absolute; top: 1%; left: 1%; height: auto">
    <div id="topItem_<?=$selectId?>" >

        <a id="selectedItem_<?=$selectId?>" class="listItem listItem_<?=$selectId?>" data-id = "<?=$selectedItem?>"
        >
            <?=$listData[$selectedItem]?>
        </a>

        <div id="items_<?=$selectId?>" class="itemsArea itemsArea_<?=$selectId?> " style="display: none">

            <?php foreach ($listData as $key => $value):?>

                <?php $disp = ($key != $selectedItem) ? 'block' : 'none'?>
                <div id="itemArea_<?=$selectId?>_<?=$key?>" class="itemArea itemArea_<?=$selectId?>" style="display: <?=$disp?>">
                    <a
                            id="<?=$selectId?>_<?=$key?>"
                            class="listItem listItem_<?=$selectId?> btnItem"
                            data-id = "<?=$key?>"
                    ><?= $value?></a>
                </div>

            <?php endforeach;?>
        </div>
    </div>
</div>
