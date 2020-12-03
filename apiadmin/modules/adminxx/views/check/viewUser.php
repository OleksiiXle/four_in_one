<?php
use yii\helpers\Html;
use \yii\widgets\DetailView;
use \apiadmin\modules\adminxx\models\UserM;
use yii\jui\JuiAsset;
use \dosamigos\datepicker\DatePicker;

JuiAsset::register($this);

$this->title = 'Профіль користувача';

?>
<style>
    .changedItemsTable{
        height: 450px;
        overflow: auto ; /* полосы прокрутки | hidden | scroll | visible | inherit*/

    }
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
                <h4><?= (!empty($userProfile['position'])) ? Html::encode($userProfile['position']) : Html::encode($userProfile['job_name'])  ?></h4>
                <h4><?= UserM::STATUS_DICT[$userProfile['status']]  ?></h4>
            </div>
            <div class="col-md-12 col-lg-2">
                <h4><?= Html::a('Повернутися', \yii\helpers\Url::toRoute('guest-control'), ['style' => 'color:red']);?></h4>
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
                            'label' => 'Логін',
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
                            'attribute' => 'spec_document',
                            'label' => 'Жетон',
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['spec_document'];
                            }
                        ],
                        [
                            'attribute' => 'phone',
                            'label' => 'Телефон',
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['phone'];
                            }
                        ],
                        [
                            'attribute' => 'direction',
                            'label' => 'Напрямок',
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['direction'];
                            }
                        ],
                        [
                            'attribute' => 'username',
                            'label' => 'Логін',
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['username'];
                            }
                        ],
                        [
                            'attribute' => 'created_at',
                            'label' => 'Зареєстрований',
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['created_at'] . ' ' . $data['userCreater'];
                            }
                        ],
                        [
                            'attribute' => 'updated_at',
                            'label' => 'Остання зміна',
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['updated_at'] . ' ' . $data['userUpdater'];
                            }
                        ],
                        [
                            'attribute' => 'firstVisitTimeTxt',
                            'label' => 'Перший візіт',
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['firstVisitTimeTxt'];
                            }
                        ],
                        [
                            'attribute' => 'lastVisitTimeTxt',
                            'label' => 'Останній візіт',
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['lastVisitTimeTxt'];
                            }
                        ],
                        [
                            'attribute' => 'lastRoute',
                            'label' => 'Останній роут',
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['lastRoute'];
                            }
                        ],
                        [
                            'attribute' => 'personal_id',
                            'label' => 'ID Працівника',
                            'format' => 'raw',
                            'value' => function($data){
                                return $data['personal_id'];
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
                    <li><a href="#tabsl-1">Підрозділи</a></li>
                    <li><a href="#tabsl-2">Ролі</a></li>
                    <li><a href="#tabsl-3">Дозвіли</a></li>
                    <li><a href="#tabsl-4">Роути</a></li>
                    <li><a href="#tabsl-5">Дії</a></li>
                </ul>
                <div id="tabsl-1" >
                    <?php if (!empty($userProfile['departments'])): ?>
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <td>Підрозділ</td>
                            <td>Редагування підрозділів</td>
                            <td>Редагування посад</td>
                            <td>Редагування працівників</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($userProfile['departments'] as $department):?>
                            <tr>
                                <td><?= $department['name'];?></td>
                                <td><?= (!empty($department['can_department'])) ? 'Так' : 'Ні';?></td>
                                <td><?= (!empty($department['can_position'])) ? 'Так' : 'Ні';?></td>
                                <td><?= (!empty($department['can_personal'])) ? 'Так' : 'Ні';?></td>
                            </tr>

                        <?php endforeach;?>
                        </tbody>
                    </table>

                    <?php endif;?>
                </div>
                <div id="tabsl-2">
                    <div>
                        <?php if (!empty($userProfile['userRoles'])): ?>
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <td>Роль</td>
                                    <td>Коментар</td>
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
                                    <td>Дозвіл</td>
                                    <td>Коментар</td>
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
                <div id="tabsl-4">
                    <div>
                        <?php if (!empty($userProfile['userRoutes'])): ?>
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <td>Роут</td>
                                    <td>Коментар</td>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($userProfile['userRoutes'] as $route):?>
                                    <tr>
                                        <td><?= $route['id'];?></td>
                                        <td><?= $route['name'];?></td>
                                    </tr>

                                <?php endforeach;?>
                                </tbody>
                            </table>

                        <?php endif;?>

                    </div>
                </div>
                <div id="tabsl-5">
                    <div id="tabs-actions" class="userRightSide ">
                        <ul>
                            <li><a href="#tabs-actions-1">Підрозділи</a></li>
                            <li><a href="#tabs-actions-2">Посади</a></li>
                            <li><a href="#tabs-actions-3">Працівники</a></li>
                        </ul>
                        <div id="tabs-actions-1" class="changedItemsTable">
                            <?php
                            echo Html::beginTag('table', [
                                'class' => 'table table-bordered table-striped '
                            ]);
                            foreach ($departments as $department){
                                echo Html::beginTag('tr');
                                echo Html::tag('td',$department['cnt'], [

                                ]);
                                echo Html::tag('td',$department['operation'], [

                                ]);
                                echo Html::tag('td',$department['name'], [

                                ]);
                                echo Html::endTag('tr');
                            }
                            echo Html::endTag('table');
                            ?>
                        </div>
                        <div id="tabs-actions-2" class="changedItemsTable">
                            <?php
                            echo Html::beginTag('table', [
                                'class' => 'table table-bordered table-striped '
                            ]);
                            foreach ($positions as $position){
                                echo Html::beginTag('tr');
                                echo Html::tag('td',$position['cnt'], [

                                ]);
                                echo Html::tag('td',$position['operation'], [

                                ]);
                                echo Html::tag('td',$position['name'], [

                                ]);
                                echo Html::endTag('tr');
                            }
                            echo Html::endTag('table');
                            ?>
                        </div>
                        <div id="tabs-actions-3" class="changedItemsTable">
                            <?php
                            echo Html::beginTag('table', [
                                'class' => 'table table-bordered table-striped '
                            ]);
                            foreach ($personal as $persona){
                                echo Html::beginTag('tr');
                                echo Html::tag('td',$persona['cnt'], [

                                ]);
                                echo Html::tag('td',$persona['operation'], [

                                ]);
                                echo Html::tag('td',$persona['name'], [

                                ]);
                                echo Html::endTag('tr');
                            }
                            echo Html::endTag('table');
                            ?>

                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $( function() {
        $( "#tabsl" ).tabs();
        $( "#tabs-actions" ).tabs();
        $( "#timePersonal" ).datepicker();

    } );
</script>
