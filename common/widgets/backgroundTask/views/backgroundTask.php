<?php
use yii\jui\JuiAsset;

JuiAsset::register($this);
?>

<div id="backgroundTask_<?=$id?>" class="info-block" style="display: none">
    <div class="container-fluid">

        <div class="row">
            <div class="info-header">
                <div class="col-lg-1" align="left">
                    <button class="btn-control glyphicon glyphicon-chevron-up" onclick = "changeView(this);"></button>
                </div>
                <div class="col-lg-10">
                    <span id="info-header" class="info-header-text"><?=$title?></span>
                </div>
                <div class="col-lg-1" align="right">
                    <button class="btn-control glyphicon glyphicon-remove" onclick = "closeInfo();"></button>
                </div>
            </div>
        </div>

        <div class="row to-hide_<?=$id?>">
            <div class="col-lg-6">
                <div class="infoArea" id="taskStatusArea_<?=$id?>"></div>
            </div>
            <div class="col-lg-6">
                <div class="infoArea" id="customStatusArea_<?=$id?>"></div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <progress id="progressArea_<?=$id?>" max="100" value="0" style="width: 100%"></progress>
            </div>
        </div>

        <div class="row  to-hide_<?=$id?>">
            <div class="col-lg-12">
                <div id="resultArea_<?=$id?>" class="result-area" style="display: none"></div>
            </div>
        </div>

        <div class="row  to-hide_<?=$id?>">
            <div class="col-lg-12">
                <div id="errorArea_<?=$id?>" class="result-area" style="display: none"></div>
            </div>
        </div>

    </div>

</div>

<script>
    $(document).ready(function(){
        $( ".info-block" ).draggable();

    });
    var position = $("#backgroundTask_<?=$id?>").position();
    $("#backgroundTask_<?=$id?>").offset({top:(position.top + 50 * parseInt(<?=$id?>)), left:(position.left + 50 * parseInt(<?=$id?>))});

    function changeView(btn) {
        if ($(btn).hasClass('glyphicon-chevron-up')) {
            $(btn).removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
            $('#taskStatusArea_<?=$id?>').css('display', 'none');
            $('.to-hide_<?=$id?>').css('display', 'none');
        } else {
            $(btn).removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
            $('.to-hide_<?=$id?>').css('display', 'block');
        }
    }

    function closeInfo() {
        $('#backgroundTask_<?=$id?>').css('display', 'none');
        $('html,body').css('cursor','default');
    }

</script>
