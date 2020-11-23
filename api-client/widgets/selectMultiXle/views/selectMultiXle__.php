<div id="<?=$selectId?>">
    <div id="choise_<?=$selectId?>" class="multi-selct-block">
        <b>Make your choise</b><br>
        <?php foreach ($itemsArray as $key => $value) :?>
            <div class="choise-row choise-row-<?=$selectId?>"
                 data-key="<?=$key?>"
                 data-choised="0"
            >
                <?=$value?>
            </div>
        <?php endforeach;?>
    </div>
    <div id="selected_<?=$selectId?>" class="multi-selct-block">
        <b>Selected</b><br>
    </div>

</div>
