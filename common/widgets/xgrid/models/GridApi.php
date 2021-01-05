<?php
namespace common\widgets\xgrid\models;

use Yii;
use common\widgets\xgrid\XgridApi;
use yii\web\BadRequestHttpException;

abstract class GridApi
{
    const RENDER_MODE_DRAW = 'draw';
    const RENDER_MODE_RELOAD = 'reload';
    const RENDER_MODE_UPLOAD = 'upload';

    protected $grid = null;
    protected $provider = null;
    public $gridConfig = null;
    private $consoleFilter = [];

    public function __construct(array $config=[])
    {
        if (isset($config['consoleFilter'])) {
            $this->consoleFilter = $config['consoleFilter'];
        }
    }

    /**
     * @return array
     */
    abstract public function gridConfig();

    /**
     * @return array
     */
    abstract public function providerConfig();

    /**
     * @param bool $reload
     * @throws \yii\base\InvalidConfigException
     */
    protected function makeGrid($renderMode)
    {
        if ($renderMode === self::RENDER_MODE_UPLOAD) {
            $providerConfig = $this->providerConfig();
            $providerConfig['usePagination'] = false;
            $providerConfig['construct'] = 'console';
            $providerConfig['consoleFilter'] = $this->consoleFilter;
            $this->provider = new GridApiDataProvider($providerConfig);
        } else {
            if ($this->provider === null) {
                $this->provider = new GridApiDataProvider($this->providerConfig());
            }
        }
        $this->gridConfig = $this->gridConfig();
        $this->gridConfig['class'] = XgridApi::class;
        $this->gridConfig['gridModel'] = static::class;
        $this->gridConfig['renderMode'] = $renderMode;
        if ($this->gridConfig['useActions']) {
            $this->gridConfig['actionsList'] = $this->getActionsList();
        }

        $this->grid = Yii::createObject($this->gridConfig);
    }

    /**
     * Действия с выделенными строками, можно переопределить и добавить свои действия
     * @return array
     */
    protected function getActions()
    {
        $actions = [
            'checkAll' => [
              'name' => Yii::t('app', 'Пометить все выбранные строки, как выделенные'),
                'do' =>  function() {
                    $this->checkAllAction();
                },
            ],
            'unCheckAll' => [
              'name' => Yii::t('app', 'Отменить выделение'),
              'do' => function(){
                  $this->unCheckAllAction();
              },
            ],
            'uploadChecked' => [
              'name' => Yii::t('app', 'Вывести выделенные в файл'),
              'do' => function(){
                  $this->uploadCheckedAction();
              },
            ],
        ];

        return $actions;
    }

    /**
     * @return array
     */
    public function getActionsList()
    {
        $ret = [];
        foreach ($this->getActions() as $key => $action) {
            $ret[$key] = $action['name'];
        }

        return $ret;
    }

    /**
     * @param $key
     * @return bool|mixed
     */
    private function doAction($key)
    {
        $action = $this->getActions()[$key];
        if ($action['do'] instanceof \Closure){
            return call_user_func($action['do']);
        }

        return false;
    }

    /**
     * Выделить все строки таблицы с учетом наложенных условий
     */
    public function checkAllAction()
    {
        $this->provider = new GridApiDataProvider($this->providerConfig());
        $this->provider->addConditionToFilter([
            'allRowsAreChecked' => true,
            'showOnlyChecked' => false,
            'checkedIds' => [],
            ]);
    }

    /**
     * Отменить выделение
     */
    public function unCheckAllAction()
    {
        $this->provider = new GridApiDataProvider($this->providerConfig());
        $this->provider->addConditionToFilter([
            'allRowsAreChecked' => false,
            'showOnlyChecked' => false,
            'checkedIds' => [],
        ]);
    }

    /**
     * Вывести выделенные строки в файл
     * @return bool
     */
    public function uploadCheckedAction()
    {
        return true;
    }

    /**
     * Нарисовать грид в представлении
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
            $this->makeGrid(self::RENDER_MODE_DRAW);
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

    /**
     * Перегрузить таблицу аяксом на основании изменения фильтра, сортировки или пагинации
     * @param $_post
     * @return string
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function reload($_post)
    {
        if (!isset($_post['action'])) {
            throw new BadRequestHttpException('Action not found');
        }

        if ($_post['action'] == 'reload') {
            $this->makeGrid(self::RENDER_MODE_RELOAD);
            $result = $this->grid->run();
            return $result;
        }

        foreach ($this->getActions() as $key => $action) {
            if ($_post['action'] == $key) {
                $this->doAction($key);
                $this->makeGrid(self::RENDER_MODE_RELOAD);
                $result = $this->grid->run();
                return $result;
            }
        }

        return "<h1>Action " . $_post['action'] . " is not declared</h1>";
    }

    /**
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function upload()
    {
        $this->makeGrid(self::RENDER_MODE_UPLOAD);
        $result = $this->grid->run();
        return $result;

    }
}