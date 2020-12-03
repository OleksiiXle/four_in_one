<?php
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\modules\adminxx\models\AuthItemX;
use app\widgets\xlegrid\Xlegrid;

?>

<?php

//\app\modules\adminxx\assets\AdminxxUpdateAuthItemAsset::register($this);

$this->title =  'Дозвіли, ролі';

?>
<div class="row ">
    <div class="xHeader">
        <div class="col-md-6" align="left">
        </div>
        <div class="col-md-6" align="right" >
            <?php
            echo Html::a('Створити роль', [\yii\helpers\Url::toRoute('/adminxx/auth-item/create'), 'type' => AuthItemX::TYPE_ROLE],
                [
                    'class' =>'btn btn-primary',
                ]);
            echo '  ';
            echo Html::a('Створити дозвіл', [\yii\helpers\Url::toRoute('/adminxx/auth-item/create'), 'type' => AuthItemX::TYPE_PERMISSION], [
                'class' =>'btn btn-primary',
            ]);
            echo '  ';
            ?>
        </div>
    </div>

</div>
<div class="row xContent">
    <div class="xCard">
        <?php Pjax::begin(['id' => 'gridPermission']);
        echo Xlegrid::widget([
            'dataProvider' => $dataProvider,
            'gridTitle' => '',
            'additionalTitle' => 'qq',
            'filterView' => '@app/modules/adminxx/views/auth-item/_authItemFilter',
            //-------------------------------------------
            'tableOptions' => [
                'class' => 'table table-bordered table-hover table-condensed',
            //  'style' => ' width: 100%; table-layout: fixed;',
            ],

            //-------------------------------------------
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'label'=>'Тип',
                    'content'=>function($data){
                        $ret = '';
                        switch ($data->type){
                            case AuthItemX::TYPE_ROLE:
                                $ret = 'Роль';
                                break;
                            case AuthItemX::TYPE_PERMISSION:
                                $ret =  'Дозвіл';
                                break;
                        }
                        return $ret;
                    },
                ],

                'name',
                'description',
                'rule_name',
                ['class' => 'yii\grid\ActionColumn',
                    'buttons'=>[
                        'update'=>function($url, $data) {
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>',
                                \yii\helpers\Url::toRoute(['/adminxx/auth-item/update',
                                    'name' => $data['name'],

                                ]  ),
                                [
                                    'title' => \Yii::t('app', 'Редагувати'),
                                ]);

                        },
                    ],
                    'template'=>' {update}',

                ],
            ],
            //------------------------------

        ]);
        Pjax::end() ?>

    </div>

</div>

