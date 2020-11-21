<?php
use common\helpers\ViewHelper;

ViewHelper::setTranslationsForJS($this, [
    'Показать список выбора',
    'Скрыть список выбора',
], 'widget-selectMultiXle');

$itemsArray = array_diff_key($itemsArrayToSelect, $itemsArrayOwn);
$msgDisplayCSSStyle = (empty($itemsArrayOwn)) ? 'active':'no-active';
?>
<div class="form-group field-<?=$textAreaAttributeId?>">
    <label class="control-label" for="<?=$textAreaAttributeId?>">
        <?=$label?>
        <a id="show-list-btn-<?=$selectId?>" href="#">
            <span class="glyphicon glyphicon-chevron-down"></span>
        </a>
    </label>
    <textarea id="<?=$textAreaAttributeId?>"
              class="form-control"
              name="<?=$textAreaAttributeName?>"
              title="<?=\Yii::t('app', 'Показать список выбора')?>"
              col="20" row="2" style="display: none">

    </textarea>
    <div id="<?=$selectId?>" class="multi-selct-block" >
        <!--*************************************************************************** выбранные наимеования -->
        <div id="selected_<?=$selectId?>" class="selected-items">
            <span id="no-selected-message-<?=$selectId?>" class="selection-row <?=$msgDisplayCSSStyle?>" >
                <?=\Yii::t('app', 'Ничего не выбрано')?>
        </span>
            <?php foreach ($itemsArrayOwn as $key => $value) :?>
                <div class="selection-row selection-row-<?=$selectId?> active"
                     data-key="<?=$key?>"
                     data-selected="1"
                >
                    <?=$value?>
                </div>

            <?php endforeach;?>
            <?php foreach ($itemsArray as $key => $value) :?>
                <div class="selection-row selection-row-<?=$selectId?> no-active"
                     data-key="<?=$key?>"
                     data-selected="0"
                >
                    <?=$value?>
                </div>
            <?php endforeach;?>
        </div>
        <!--*************************************************************************** наименования для выбора -->
        <div id="choise_<?=$selectId?>" class="choise-items" style="display: none">
            <hr style="margin: 1px">
            <?php foreach ($itemsArrayOwn as $key => $value) :?>
                <div class="choise-row choise-row-<?=$selectId?> no-active"
                     data-key="<?=$key?>"
                     data-choised="1"
                >
                    <?=$value?>
                </div>
            <?php endforeach;?>
            <?php foreach ($itemsArray as $key => $value) :?>
                <div class="choise-row choise-row-<?=$selectId?> active"
                     data-key="<?=$key?>"
                     data-choised="0"
                >
                    <?=$value?>
                </div>
            <?php endforeach;?>
        </div>
        <!--***************************************************************************-->
    </div>

</div>

