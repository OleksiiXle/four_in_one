<?php
use yii\grid\GridView;

$this->title =  'Oauth';
?>

<div class="container-fluid">

    <div class="row">
        <div class="col-md-12 xContent">
            <b>OauthCient</b>
            <div class="xCard" style="height: 10%; overflow: auto">
                <?=
                GridView::widget([
                    'dataProvider' => $dataProviderClient,
                    'tableOptions' => [
                        'class' => 'table table-bordered table-hover table-condensed',
                    ],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'client_id',
                        'client_secret',
                        'redirect_uri',
                        'grant_type',
                        'scope',
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12 xContent">
            <b>OauthCient</b>
            <div class="xCard" style="height: 10%; overflow: auto">
                <?=
                GridView::widget([
                    'dataProvider' => $dataProviderAuthCode,
                    'tableOptions' => [
                        'class' => 'table table-bordered table-hover table-condensed',
                    ],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'authorization_code',
                        'client_id',
                        'redirect_uri',
                        'user_id',
                        'username',
                        'expiresDataTime',
                        'scope',
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12 xContent">
            <b>OauthCient</b>
            <div class="xCard" style="height: 10%; overflow: auto">
                <?=
                GridView::widget([
                    'dataProvider' => $dataProviderAccessToken,
                    'tableOptions' => [
                        'class' => 'table table-bordered table-hover table-condensed',
                    ],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'access_token',
                        'client_id',
                        'user_id',
                        'username',
                        'expiresDataTime',
                        'scope',
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>



    <div class="row">
        <div class="col-md-12 xContent">
            <b>OauthCient</b>
            <div class="xCard" style="height: 10%; overflow: auto">
                <?=
                GridView::widget([
                    'dataProvider' => $dataProviderRefreshToken,
                    'tableOptions' => [
                        'class' => 'table table-bordered table-hover table-condensed',
                    ],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'refresh_token',
                        'client_id',
                        'user_id',
                        'username',
                        'expiresDataTime',
                        'scope',
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>

</div>
