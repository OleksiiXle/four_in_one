<?php
use yii\grid\GridView;

$this->title =  'Oauth';
?>
<style>
    .oauthData{
        margin: 3px;
        padding: 5px;
        overflow: auto;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 xContent">
            <b>OauthCient</b>
            <div class="xCard" style="height: 300px; overflow: auto">
                <?php
                echo $oauthClientGrid->drawGrid();
                ?>
            </div>
        </div>
    </div>
    <div class="row xCard" style="height: 400px; padding: 5px; margin: 3px;">
        <div class="col-lg-4">
            <b>OauthAuthorizationCode</b>
            <div class="xCard oauthData" >
                <?=
                GridView::widget([
                    'dataProvider' => $dataProviderAuthCode,
                    'tableOptions' => [
                        'class' => 'table table-bordered table-hover table-condensed',
                    ],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'client_id',
                        'user_id',
                        'username',
                        'redirect_uri',
                        'expiresDataTime',
                        'authorization_code',
                        'scope',
                    ],
                ]);
                ?>
            </div>
        </div>
        <div class="col-lg-4">
            <b>OauthAccessToken</b>
            <div class="xCard oauthData">
                <?=
                GridView::widget([
                    'dataProvider' => $dataProviderAccessToken,
                    'tableOptions' => [
                        'class' => 'table table-bordered table-hover table-condensed',
                    ],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'client_id',
                        'user_id',
                        'username',
                        'expiresDataTime',
                        'access_token',
                        'scope',
                    ],
                ]);
                ?>
            </div>

        </div>
        <div class="col-lg-4">
            <b>OauthRefreshToken</b>
            <div class="xCard oauthData">
                <?=
                GridView::widget([
                    'dataProvider' => $dataProviderRefreshToken,
                    'tableOptions' => [
                        'class' => 'table table-bordered table-hover table-condensed',
                    ],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'client_id',
                        'user_id',
                        'username',
                        'expiresDataTime',
                        'refresh_token',
                        'scope',
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>
</div>
