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
use yii\web\BadRequestHttpException;

class UsersGrid
{
    private $grid = null;
    private $provider = null;
    public $providerConfig = [
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


    public function setGridConfig($reload = false)
    {
        if ($this->provider === null) {
            $this->provider = new GridDataProvider($this->providerConfig);
        }
        $this->gridConfig = [
            'name' => 'usersGrid',
            'dataProvider' => $this->provider,
            'useAjax' => true,
            'useActions' => true,
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
            'filterView' => '@app/modules/adminxx/views/user/_filterUser',
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
                                    'Переглянути консерву' => [
                                        'icon' => 'glyphicon glyphicon-lock',
                                        'route' => Url::toRoute(['/adminxx/user/conservation', 'user_id' => $data['id']]),
                                    ],

                                ],
                                'offset' => -200,

                            ]
                        );
                    },
                ],
            ],
        ];
        if ($this->gridConfig['useActions']) {
            $this->gridConfig['actionsList'] = $this->getActionsList();
        }
        $this->gridConfig['class'] = Xgrid::class;
        if ($reload) {
            $this->gridConfig['reload'] = true;
        }
    }

    public function makeGrid()
    {
        $this->grid = Yii::createObject($this->gridConfig);
    }

    public function getActions()
    {
        $actions = [
            'checkAll' => [
              'name' => Yii::t('app', 'Пометить все выбранные строки, как выделенные'),
                'do' =>  function() {
                    return $this->checkAllAction();
                },
            ],
            'unCheckAll' => [
              'name' => Yii::t('app', 'Отменить выделение'),
              'do' => function(){
                  return $this->unCheckAllAction();
              },
            ],
            'uploadChecked' => [
              'name' => Yii::t('app', 'Вывести выделенные в файл'),
              'do' => function(){
                  return $this->uploadCheckedAction();
              },
            ],
        ];

        return $actions;
    }

    public function getActionsList()
    {
        $ret = [];
        foreach ($this->getActions() as $key => $action) {
            $ret[$key] = $action['name'];
        }

        return $ret;
    }

    public function doAction($key)
    {
        $action = $this->getActions()[$key];
        if ($action['do'] instanceof \Closure){
            return call_user_func($action['do']);
        }

        return false;
    }

    public function checkAllAction()
    {
        $tmp = 1;
        $this->provider = new GridDataProvider($this->providerConfig);
        $this->provider->addConditionToFilter([
            'allRowsAreChecked' => true,
            'showOnlyChecked' => false,
            'checkedIds' => [],
            ]);
    }

    public function unCheckAllAction()
    {
        $tmp = 1;
        $this->provider = new GridDataProvider($this->providerConfig);
        $this->provider->addConditionToFilter([
            'allRowsAreChecked' => false,
            'showOnlyChecked' => false,
            'checkedIds' => [],
        ]);
    }

    public function uploadCheckedAction()
    {
        return true;
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
            $this->setGridConfig();
            $this->makeGrid();
            $result = $this->grid->run();
            $out = $this->grid->afterRun($result);
        } catch (\Exception $e) {
            // close the output buffer opened above if it has not been closed already
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $e;
        }

        return ob_get_clean() . $out;
    }

    public function reload($_post)
    {
        if (!isset($_post['action'])) {
            throw new BadRequestHttpException('Action not found');
        }

        if ($_post['action'] == 'reload') {
            $this->setGridConfig(true);
            $this->makeGrid();
            $result = $this->grid->run();
            return $result;
        }

        foreach ($this->getActions() as $key => $action) {
            if ($_post['action'] == $key) {
                $this->doAction($key);
                $this->setGridConfig(true);
                $this->makeGrid();
                $result = $this->grid->run();
                return $result;
            }
        }

        return "<h1>Action " . $_post['action'] . " is not declared</h1>";
    }


}