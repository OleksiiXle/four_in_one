<?php
use yii\helpers\Html;
use \yii\widgets\DetailView;

$this->title = 'Фонове завдання';

?>
<style>
    .userFIOArea{
        margin-top: 10px;
        margin-bottom: 10px;
      /*  background-color: lightgrey;*/
        padding: 10px;
    }
    .userDataArea{
        margin-top: 10px;
        margin-bottom: 10px;
        background-color: lightgrey;
        padding: 10px;
    }
    .userRightSide{
        margin-top: 10px;
        margin-bottom: 10px;
        background-color: transparent;
        padding: 10px;
        box-shadow: 0 4px 5px 0 rgba(0, 0, 0, 0.14), 0 1px 10px 0 rgba(0, 0, 0, 0.12), 0 2px 4px -1px rgba(0, 0, 0, 0.2);


    }
    .userDepartmentsArea{
        margin-top: 10px;
        margin-bottom: 10px;
        background-color: aliceblue;
        padding: 10px;

    }
    .userRolesPermissionsArea{
        margin-top: 10px;
        background-color: lemonchiffon;
        padding: 10px;

    }
    .formButtons{
        margin-top: 10px;
        padding: 10px;
    }
</style>

<div class="container-fluid">
   <div class="row">
        <!--*************************************************************************** ЛЕВАЯ ПОЛОВИНА -->
       <h4><?= Html::a('До списку завдань', 'index', ['style' => 'color:red']);?></h4>

       <div class="col-md-12 col-lg-4">
            <div class="ui-corner-all userDataArea xCard">
                <?php
                echo DetailView::widget([
                    'model' => $task,
                    'attributes' => [
                        'id',
                        'pid',
                        'user_id',
                        'model',
                        'arguments',
                        'status',
                        'result_file',
                        'result_file_pointer',
                        'progress',
                        'result',
                        'datetime_create',
                        'datetime_update',
                        'time_limit',
                    ],
                ]);
                ?>
            </div>

        </div>
        <!--*************************************************************************** ПРАВАЯ ПОЛОВИНА -->
   </div>
</div>
