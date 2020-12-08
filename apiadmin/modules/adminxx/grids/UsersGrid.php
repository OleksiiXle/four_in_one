<?php
namespace apiadmin\modules\adminxx\grids;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use apiadmin\modules\adminxx\models\filters\UserFilter;
use common\widgets\xgrid\models\GridDataProvider;
use common\models\UserM;
use common\widgets\menuAction\MenuActionWidget;
use common\widgets\xgrid\Xgrid;

class UsersGrid
{
    public $providerConfig =
        [
            // 'searchId' => $id,
            'filterModelClass' => UserFilter::class,
            'conserveName' => 'userAdminGrid',
            'pageSize' => 5,
            'sort' => ['attributes' => [
                'id',
                'username',
                'nameFam' => [
                    'asc' => [
                        'user_data.last_name' => SORT_ASC,
                    ],
                    'desc' => [
                        'user_data.last_name' => SORT_DESC,
                    ],
                ],
                'lastRoutTime' => [
                    'asc' => [
                        'user_data.last_rout_time' => SORT_ASC,
                    ],
                    'desc' => [
                        'user_data.last_rout_time' => SORT_DESC,
                    ],
                ],
                'lastRout' => [
                    'asc' => [
                        'user_data.last_rout' => SORT_ASC,
                    ],
                    'desc' => [
                        'user_data.last_rout' => SORT_DESC,
                    ],
                ],
                'status' => [
                    'asc' => [
                        'user.status' => SORT_ASC,
                    ],
                    'desc' => [
                        'user.status' => SORT_DESC,
                    ],
                ],
            ]],
        ];
    private $gridConfig = null;

    public function __construct()
    {
        $this->gridConfig = $this->getGridConfig();
        $this->gridConfig['class'] = Xgrid::class;
    }

    public function getGridConfig()
    {
        return
            [
                'name' => 'usersGrid',
                'dataProvider' => new GridDataProvider($this->providerConfig),
                'useAjax' => true,
                'useCheckForRows' => true,
                'checkActionList' => [
                    'actions' => [
                        'action1' => 'action1***',
                        'action2' => 'action2***',
                        'action3' => 'action3***',
                    ],
                    'options' => [
                        'class' => 'checkActionsSelect',
                        'onchange' => 'actionWithChecked(this);',
                    ],
                ],
                'pager' => [
                    'firstPageLabel' => '<<<',
                    'lastPageLabel'  => '>>>'
                ],
                'gridTitle' => '',
                'additionalTitle' => 'qq',
                'filterView' => '@app/modules/adminxx/views/user/_filterUser',
                //-------------------------------------------
                'tableOptions' => [
                    'class' => 'table table-bordered table-hover table-condensed',
                    'style' => ' width: 100%; table-layout: fixed;',
                ],
                //-------------------------------------------
                'columns' => [
                    [
                        'label' => '',
                        'headerOptions' => ['style' => 'width: 2%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 2%; white-space: nowrap; overflow: hidden;'],
                        'options' => ['class' => 'row-check'],
                        //'content' => '',
                    ],
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'headerOptions' => ['style' => 'width: 3%;'],
                        'contentOptions' => ['style' => 'width: 3%;'],
                    ],
                    [
                        'attribute' => 'id',
                        'headerOptions' => ['style' => 'width: 3%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 3%; overflow: hidden'],
                    ],
                    [
                        'attribute' => 'username',
                        'headerOptions' => ['style' => 'width: 10%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 10%; overflow: hidden'],
                    ],
                    [
                        'attribute' => 'nameFam',
                        'headerOptions' => ['style' => 'width: 10%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 10%; overflow: hidden'],
                    ],
                    [
                        'attribute' => 'nameNam',
                        'headerOptions' => ['style' => 'width: 7%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 7%; overflow: hidden'],
                    ],
                    [
                        'attribute' => 'nameFat',
                        'headerOptions' => ['style' => 'width: 7%; overflow: hidden;'],
                        'contentOptions' => ['style' => 'width: 7%; overflow: hidden'],
                    ],
                    [
                        'attribute' => 'userRoles',
                        'headerOptions' => ['style' => 'width: 8%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 8%; overflow: hidden;'],
                    ],
                    [
                        'attribute' => 'lastVisitTimeTxt',
                        'label' => 'Час ост. дії',
                        'headerOptions' => ['style' => 'width: 8%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 8%; white-space: nowrap; overflow: hidden;'],
                    ],
                    [
                        'attribute' => 'created_at_str',
                        'headerOptions' => ['style' => 'width: 7%; overflow: hidden;'],
                        'contentOptions' => ['style' => 'width: 7%; overflow: hidden'],
                    ],
                    [
                        'attribute' => 'status',
                        'headerOptions' => ['style' => 'width: 6%;overflow: hidden; '],
                        'contentOptions' => ['style' => 'width: 6%; white-space: nowrap; overflow: hidden;'],
                        'label'=>'Активність',
                        'content'=>function($data){
                            return Html::a('<span class="glyphicon glyphicon-star"></span>', false,
                                [
                                    'style' => ($data->status == UserM::STATUS_ACTIVE)
                                        ? 'color: red;' : 'color: grey;',
                                    'title' => ($data->status == UserM::STATUS_ACTIVE)
                                        ? 'Активувати' : 'Деактивувати',
                                    'onclick' => 'changeUserActivity("' . $data->id . '");',
                                    'id' => 'activityIcon_' . $data->id,
                                ]);
                        },
                    ],
                    [
                        'headerOptions' => ['style' => 'width: 3%; '],
                        'contentOptions' => [
                            'style' => 'width: 3%; ',
                        ],
                        'label'=>'',
                        'content'=>function($data){
                            return MenuActionWidget::widget(
                                [
                                    'items' => [
                                        'Перегляд інформації' => [
                                            'icon' => 'glyphicon glyphicon-eye-open',
                                            'route' => Url::toRoute(['/adminxx/user/view', 'id' => $data['id']]),
                                        ],
                                        'Змінити данні' => [
                                            'icon' => 'glyphicon glyphicon-pencil',
                                            'route' => Url::toRoute(['/adminxx/user/update-by-admin',
                                                'mode' => 'update', 'id' => $data['id'],]),
                                        ],
                                        'Змінити дозвіли та ролі' => [
                                            'icon' => 'glyphicon glyphicon-lock',
                                            'route' => Url::toRoute(['/adminxx/user/update-user-assignments', 'id' => $data['id']]),
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
     * Creates a widget instance and runs it.
     * The widget rendering result is returned by this method.
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @return string the rendering result of the widget.
     * @throws \Exception
     */
    public function drawGrid()
    {
        ob_start();
        ob_implicit_flush(false);
        try {
            /* @var $widget Widget */
            $widget = Yii::createObject($this->gridConfig);
            $result = $widget->run();
            $out = $widget->afterRun($result);
        } catch (\Exception $e) {
            // close the output buffer opened above if it has not been closed already
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $e;
        }

        return ob_get_clean() . $out;
    }

    public function reload()
    {
        $this->gridConfig['reload'] = true;
        $widget = Yii::createObject($this->gridConfig);
        $result = $widget->run();

        return $result;
    }


}