<?php
use yii\helpers\Html;
use app\widgets\xlegrid\Xlegrid;
use app\widgets\menuAction\MenuActionWidget;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Посты');
?>

<div class="container-fluid">
    <div class="row ">
        <div class="xHeader">
            <div class="col-md-6" align="left">
            </div>
            <div class="col-md-6" align="right" >
                <?php
                echo Html::a('Добавить новый', '/post/post/create', [
                    'class' =>'btn btn-primary',
                ]);
                ?>
            </div>
        </div>
    </div>
    <div class="row xContent" style="overflow: auto">
        <?php foreach ($response['data'] as $post):?>
            <div class="row">
                <h3><?=$post['name']?></h3><br>
                <h4><?=$post['content']?></h4>
            </div>

        <?php endforeach;?>

        ?>
        <?php
        echo var_dump($response);
        ?>
    </div>
</div>





