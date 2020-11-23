<?php
use app\assets\BackgroundTaskAsset;
use \yii\helpers\Json;

BackgroundTaskAsset::register($this);

$_arguments = Json::htmlEncode($arguments);
$_model = addcslashes($model, '\\');
$this->registerJs(
        "var _arguments = '{$_arguments}';"
        . 'var _model = "' . $_model . '";'
,\yii\web\View::POS_HEAD);

?>

<style>
    #addResultArea{
        margin-top: 10px;
        margin-bottom: 10px;
        background-color: lightgrey;
        padding: 20px;
        height: 250px;
        overflow: auto;
        box-shadow: 0 4px 5px 0 rgba(0, 0, 0, 0.14), 0 1px 10px 0 rgba(0, 0, 0, 0.12), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
    }
    #errorArea{
        margin-top: 10px;
        margin-bottom: 10px;
        background-color: lightgrey;
        padding: 10px;
        height: 250px;
        overflow: auto;
        box-shadow: 0 4px 5px 0 rgba(0, 0, 0, 0.14), 0 1px 10px 0 rgba(0, 0, 0, 0.12), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
    }

    .infoArea {
        color: blue;
        font-size: large;
        padding: 20px;
        background-color: lightgrey;
    }
</style>

<div class="row">
    <h3>
        <b><?=$mode?></b>
    </h3>
    <?php
    if ($mode == 'Run background task processing with waiting with AJAX') {
        echo \yii\helpers\Html::button('Start task',
            [
                'id' => 'btnStartAjax',
                'class' => 'btn btn-primary',
                'style' => ' margin: 20px'
            ]);
    }

    ?>
</div>

<div class="row">
    <div class="col-lg-3">
        <h4>Progress value</h4>
        <div class="infoArea" id="progressValueArea"></div>
    </div>
    <div class="col-lg-3">
        <h4>AJAX counter</h4>
        <div class="infoArea" id="ajaxCounterArea"></div>
    </div>
    <div class="col-lg-3">
        <h4>Background task status</h4>
        <div class="infoArea" id="taskStatusArea"></div>
    </div>
    <div class="col-lg-3">
        <h4>Custom status</h4>
        <div class="infoArea" id="customStatusArea"></div>
    </div>
</div>

<div class="row">
    <h4>Progress bar</h4>
    <progress id="progressArea" max="100" value="0" style="width: 100%"></progress>
</div>

<div class="row">
    <h4>Result text</h4>
    <div id="addResultArea"></div>
</div>

<div class="row">
    <h4>Errors text</h4>
    <div id="errorArea" style="display: none"></div>
</div>

<div class="infoArea" class="row">
    <h4>Task Result</h4>
    <?php
    echo var_dump($result);
    ?>
</div>

<script>
    var params = {
        checkProgressInterval: 2000,
        urlStartBackgroundTask: '/background-tasks/start-task',
        urlGetTaskProgress: '/background-tasks/check-task',
        model: _model ,
        arguments: _arguments,
        _csrf: $('meta[name="csrf-token"]').attr('content'),

        taskStatusArea: $('#taskStatusArea'),
        resultArea: $('#addResultArea'),
        errorsArea: $('#errorArea'),
        progressValueArea: $('#progressValueArea'),
        ajaxCounterArea: $('#ajaxCounterArea'),
        progressArea : $('#progressArea'),
        customStatusArea: $('#customStatusArea'),

        showTaskStatusArea : true,
        showCustomStatusArea : true,
        showProgressValueArea : true,
        showAjaxCounterArea : true,
        showResultArea : true,
        showProgressArea : true,
        showErrorsArea : true,


        showPreloader : function () {
            $('button').prop('disabled', true);
            $('html,body').css('cursor','wait');
        },
        hidePreloader : function () {
            $('button').prop('disabled', false);
            $('html,body').css('cursor','default');
        },
        doOnSuccesss : function (response) {
            console.log('on success function:');
            console.log(response);

        },
        doOnError : function (response) {
            console.log('on error function:');
            console.log(response);
        }
    };

    var bt = new BackgroundTask(params);
    bt.init();

    $("#btnStartAjax").on('click', function () {
        bt.start();
    });

</script>