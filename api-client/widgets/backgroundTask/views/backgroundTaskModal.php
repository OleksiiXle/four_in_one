<div id="modal-background_<?=$id?>" class="modal-background">
    <div id="backgroundTask_<?=$id?>" class="info-block-modal" style="display: none">
        <div class="container-fluid">
            <div class="row">
                <div class="info-header">
                    <div class="col-lg-11">
                        <span id="info-header" class="info-header-text"><?=$title?></span>
                    </div>
                    <div class="col-lg-1" align="right">
                        <button id="close-btn" class="btn-control glyphicon glyphicon-remove" ></button>
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

</div>

<script>
    $(document).ready(function(){

    });
</script>
