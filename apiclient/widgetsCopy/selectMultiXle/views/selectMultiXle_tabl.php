<div id="<?=$selectId?>">
    <table>
        <tr>
            <th>
                Make your choise
            </th>
            <th>
                Your choise
            </th>
        </tr>

        <tr>
            <td>
                <table id="choiseTable_<?=$selectId?>" class="table table-condensed">
                    <?php foreach ($itemsArray as $key => $value) :?>
                        <tr>
                            <td class="choise-row choise-row-<?=$selectId?>"
                                data-key="<?=$key?>"
                                data-choised="0"
                            >
                                <?=$value?>
                            </td>
                        </tr>
                    <?php endforeach;?>
                </table>
            </td>
            <td>
                <table id="selectionTable_<?=$selectId?>" class="table table-condensed">
                    <?php foreach ($itemsArray as $key => $value) :?>
                        <tr>
                            <td class="selection-row selection-row-<?=$selectId?>"
                                data-key="<?=$key?>"
                                data-selected="0"
                                style="display: none"
                            >
                                <?=$value?>
                            </td>
                        </tr>
                    <?php endforeach;?>
                </table>
            </td>
        </tr>
    </table>
</div>
