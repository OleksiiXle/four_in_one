<?php
use yii\helpers\Html;
use \yii\widgets\DetailView;
use common\models\UserM;
use yii\jui\JuiAsset;
use \apiadmin\modules\adminxx\assets\AdminxxUserAsset;

JuiAsset::register($this);
AdminxxUserAsset::register($this);

$this->title = Yii::t('app', 'Профиль пользователя');

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
        <div class="userFIOArea">
             <div class="col-md-12 col-lg-10">
                <h3><?= Html::encode($userProfile['last_name'] . ' ' . $userProfile['first_name'] . ' ' . $userProfile['middle_name'])  ?></h3>
                <h4><?= UserM::getStatusDict()[$userProfile['status']]  ?></h4>
            </div>
            <div class="col-md-12 col-lg-2">
                <h4><?= Html::a(Yii::t('app', 'Вернуться'), \yii\helpers\Url::toRoute('index'), ['style' => 'color:red']);?></h4>
            </div>
        </div>
    </div>
    <div class="row">
        <!--*************************************************************************** ЛЕВАЯ ПОЛОВИНА -->
        <div class="col-md-12 col-lg-4">
            <div class="ui-corner-all userDataArea xCard">
                <?php
                echo DetailView::widget([
                    'model' => $userProfile,
                    'attributes' => [
                        'id',
                        [
                            'attribute' => 'username',
                            'label' => Yii::t('app', 'Логин'),
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['username'];
                            }
                        ],
                        [
                            'attribute' => 'email',
                            'label' => 'email',
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['email'];
                            }
                        ],
                        [
                            'attribute' => 'phone',
                            'label' => Yii::t('app', 'Телефон'),
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['phone'];
                            }
                        ],
                        [
                            'attribute' => 'created_at',
                            'label' => Yii::t('app', 'Зарегистрирован'),
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['created_at'] . ' ' . $data['userCreater'];
                            }
                        ],
                        [
                            'attribute' => 'updated_at',
                            'label' => Yii::t('app', 'Изменен'),
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['updated_at'] . ' ' . $data['userUpdater'];
                            }
                        ],
                        [
                            'attribute' => 'firstVisitTimeTxt',
                            'label' => Yii::t('app', 'Первое посещение'),
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['firstVisitTimeTxt'];
                            }
                        ],
                        [
                            'attribute' => 'lastVisitTimeTxt',
                            'label' => Yii::t('app', 'Последнее посещение'),
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['lastVisitTimeTxt'];
                            }
                        ],
                        [
                            'attribute' => 'lastRoute',
                            'label' => Yii::t('app', 'Последний роут'),
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['lastRoute'];
                            }
                        ],
                    ],
                ]);
                ?>
            </div>

        </div>
        <!--*************************************************************************** ПРАВАЯ ПОЛОВИНА -->
        <div class="col-md-12 col-lg-8">
            <div id="tabsl" class="userRightSide ">
                <!--*************************************************************************** МЕНЮ -->
                <ul>
                    <li><a href="#tabsl-2"><?=Yii::t('app', 'Роли')?></a></li>
                    <li><a href="#tabsl-3"><?=Yii::t('app', 'Разрешения')?></a></li>
                </ul>
                <div id="tabsl-2">
                    <div>
                        <?php if (!empty($userProfile['userRoles'])): ?>
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <td><?=Yii::t('app', 'Роль')?></td>
                                    <td><?=Yii::t('app', 'Комментарий')?></td>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($userProfile['userRoles'] as $role):?>
                                    <tr>
                                        <td><?= $role['id'];?></td>
                                        <td><?= $role['name'];?></td>
                                    </tr>

                                <?php endforeach;?>
                                </tbody>
                            </table>

                        <?php endif;?>

                    </div>
                </div>
                <div id="tabsl-3">
                    <div>
                        <?php if (!empty($userProfile['userPermissions'])): ?>
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <td><?=Yii::t('app', 'Разрешение')?></td>
                                    <td><?=Yii::t('app', 'Комментарий')?></td>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($userProfile['userPermissions'] as $permission):?>
                                    <tr>
                                        <td><?= $permission['id'];?></td>
                                        <td><?= $permission['name'];?></td>
                                    </tr>

                                <?php endforeach;?>
                                </tbody>
                            </table>

                        <?php endif;?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $( function() {
        $( "#tabsl" ).tabs();
    } );
</script>
