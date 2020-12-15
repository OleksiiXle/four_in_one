<?php

namespace apiadmin\modules\adminxx\grids;

use Yii;
use yii\helpers\Url;
use common\widgets\xgrid\models\Grid;
use common\widgets\menuAction\MenuActionWidget;
use common\widgets\menuAction\MenuActionAsset;
use apiadmin\modules\adminxx\models\AuthItemX;
use apiadmin\modules\adminxx\models\filters\AuthItemFilter;

class AuthItemGrid extends Grid
{
    /**
     * @return array
     */
    public function gridConfig()
    {
        return [
            'name' => 'authItemGrid',
            'dataProvider' => $this->provider,
            'useAjax' => true,
            'useActions' => true,
            'useCustomUploadFunction' => false,
            'assetsToRegister' => [MenuActionAsset::class],
            'primaryKey' => 'name',
            'filterView' => '@app/modules/adminxx/views/auth-item/_authItemFilter',
            //-------------------------------------------
            'columns' => [
                [
                    'label' => '',
                    'headerOptions' => ['style' => 'width: 2%;overflow: hidden; '],
                    'contentOptions' => ['style' => 'width: 2%; white-space: nowrap; overflow: hidden;'],
                    'options' => ['class' => 'row-check'],
                ],
                [
                    'class' => 'yii\grid\SerialColumn',
                    'headerOptions' => ['style' => 'width: 3%;'],
                    'contentOptions' => ['style' => 'width: 3%;'],
                ],
                [
                    'label'=>'Тип',
                    'content'=>function($data){
                        $ret = '';
                        switch ($data->type){
                            case AuthItemX::TYPE_ROLE:
                                $ret = \Yii::t('app', 'Роль');
                                break;
                            case AuthItemX::TYPE_PERMISSION:
                                $ret = \Yii::t('app', 'Разрешение');
                                break;
                        }
                        return $ret;
                    },
                ],
                'name',
                'description',
                'rule_name',
                [
                    'label'=>'',
                    'headerOptions' => ['style' => 'width: 3%; '],
                    'contentOptions' => [
                        'style' => 'width: 3%; ',
                    ],
                    'content'=>function($data){
                        return MenuActionWidget::widget(
                            [
                                'assetsRegister' => false,
                                'items' => [
                                    Yii::t('app', 'Изменить данные') => [
                                        'icon' => 'glyphicon glyphicon-pencil',
                                        'route' => Url::toRoute(['/adminxx/auth-item/update', 'name' => $data['name']]),
                                    ],
                                ],
                                'offset' => -200,
                            ]
                        );
                    },
                ],
            ],
        ];
    }
    /**
     * @return array
     */
    public function providerConfig()
    {
        return [
            'filterModelClass' => AuthItemFilter::class,
            'primaryKey' => 'name',
            'conserveName' => 'authItemAdminGrid',
            'pageSize' => 15,
            ];
    }

}