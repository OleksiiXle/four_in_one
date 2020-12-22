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
            <td id="choise_<?=$selectId?>">
                <?php foreach ($itemsArray as $key => $value) :?>
                     <div class="choise-row choise-row-<?=$selectId?>"
                           data-key="<?=$key?>"
                           data-choised="0"
                        >
                             <?=$value?>
                    </div>
                <?php endforeach;?>
            </td>
            <td id="selection_<?=$selectId?>">
                <?php foreach ($itemsArray as $key => $value) :?>
                    <div class="selection-row selection-row-<?=$selectId?>"
                         data-key="<?=$key?>"
                         data-selected="0"
                         style="display: none"
                    >
                        <?=$value?>
                    </div>
                <?php endforeach;?>
            </td>
    </table>
</div>
