<?php
use yii\helpers\Html;


$_csrfT = \Yii::$app->request->csrfToken;
$_params = \yii\helpers\Json::htmlEncode($params);
$this->registerJs("
    var _params_$menu_id  =    {$_params};
    var _menu_id          = '{$menu_id}';
    var _csrfT            = '{$_csrfT}';
",\yii\web\View::POS_HEAD);


?>




<div class="container-fluid">
    <div class="row">
        <div id="<?=$menu_id;?>" class="xtree">

        </div>
    </div>
    <?php if ($params['mode'] === 'update'):?>
        <div id="actionButtons_<?=$menu_id;?>" class="row" align="center" style="padding: 15px">
            <?php
            echo Html::button('<span class="glyphicon glyphicon-plus"></span>', [
                'title' => \Yii::t('app', 'Добавить потомка'),
                'id' => 'btn_' . $menu_id . '_appendChild',
                'class' => 'actionBtn',
              //  'target' => 'main-modal-md',
            ]);
            echo Html::button('<span class="glyphicon glyphicon-download-alt"></span>', [
                'title' => \Yii::t('app', 'Добавить брата вниз'),
                'id' => 'btn_' . $menu_id . '_appendBrother',
                'class' => 'actionBtn ',
            ]);
            echo Html::button('<span class="glyphicon glyphicon-thumbs-up"></span>', [
                'title' => \Yii::t('app', 'Поднять на уровень выше - сделать меня старшим братом моего родителя'),
                'id' => 'btn_' . $menu_id . '_levelUp',
                'class' => 'actionBtn ',
            ]);
            echo Html::button('<span class="glyphicon glyphicon-thumbs-down"></span>', [
                'title' => \Yii::t('app', 'Опустить на уровень вниз - сделать меня первым сыном моего младшего брата'),
                'id' => 'btn_' . $menu_id . '_levelDown',
                'class' => 'actionBtn ',
            ]);
            echo Html::button('<span class="glyphicon glyphicon-menu-up"></span>', [
                'title' => \Yii::t('app', 'Поднять в своем уровне'),
                'id' => 'btn_' . $menu_id . '_moveUp',
                'class' => 'actionBtn ',
            ]);
            echo Html::button('<span class="glyphicon glyphicon-menu-down"></span>', [
                'title' => \Yii::t('app', 'Опустить в своем уровне'),
                'id' => 'btn_' . $menu_id . '_moveDown',
                'class' => 'actionBtn ',
            ]);
            echo Html::button('<span class="glyphicon glyphicon-pencil"></span>', [
                'title' => \Yii::t('app', 'Изменить'),
                'id' => 'btn_' . $menu_id . '_modalOpenMenuUpdate',
                'class' => 'actionBtn ',
            ]);
            echo Html::button('<span class="glyphicon glyphicon-trash"></span>', [
                'title' => \Yii::t('app', 'Удалить вместе с потомками'),
                'id' => 'btn_' . $menu_id . '_deleteItem',
                'class' => 'actionBtn ',
            ]);
            ?>
        </div>



    <?php endif;?>
</div>
