<?php
namespace common\widgets\xlegrid;

use common\assets\BackgroundTaskAsset;
use common\widgets\xlegrid\models\GridUploadWorker;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\PjaxAsset;

class Xlegrid extends GridView
{
    public $filterPosition = self::FILTER_POS_HEADER;
    public $filterView;// = '@app/views/dictionary/_search';
    public $gridTitle = '';
    public $additionalTitle = null;
    public $filterRenderOptions = [
        'class' => 'table table-bordered',
        'style' => 'background: none',
    ];
    public $usePjax = false;
    public $pjaxContainerId = '';
    public $pjaxEnablePushState = true;
    public $pjaxEnableReplaceState = false;
    public $pjaxTimeout = 1000;
    public $pjaxScrollTo = false;
    public $pjaxClientOptions;
    public $useCheckForRows = false;
    public $checkActionList = [
        /*
        'actions' => [
            'action1' => 'action1***',
            'action2' => 'action2***',
            'action3' => 'action3***',
        ],
        'options' => [
            'onchange' => 'actionWithChecked(this);',
        ],
        */
    ];
   private $checkedIds = [];

    public function run()
    {
        $r=1;
        $view = $this->getView();
        $js = "
            const USE_PJAX = " . $this->usePjax . ";
            const PJAX_CONTAINER_ID = '#" . $this->pjaxContainerId . "';
            
            var _filterClassShortName = '" . $this->dataProvider->filterClassShortName . "';
            
        ";
        if (!empty($this->dataProvider->filterModel)){
            if ($this->dataProvider->filterModel->hasProperty('checkedIdsJSON')) {
                $this->checkedIds = json_decode($this->dataProvider->filterModel->checkedIdsJSON);
            }

            $js .= PHP_EOL . "var _filterModel = '" . addcslashes($this->dataProvider->filterModelClass, '\\') . "';";
            $js .= PHP_EOL . "var _workerClass = '" . addcslashes(GridUploadWorker::class, '\\') . "';";
        }
        $view->registerJs($js,\yii\web\View::POS_HEAD);
        if ($this->usePjax) {
            $this->registerPjaxScript();
        }
        XlegridAsset::register($view);
        BackgroundTaskAsset::register($view);
        parent::run();
    }

    private function registerPjaxScript()
    {
        $clientOptions = [
            'push' => $this->pjaxEnablePushState,
            'replace' => $this->pjaxEnableReplaceState,
            'timeout' => $this->pjaxTimeout,
            'scrollTo' => $this->pjaxScrollTo,
            'container' => "#$this->pjaxContainerId",
            'fragment' =>  "#$this->pjaxContainerId",
        ];
        $options = Json::htmlEncode($clientOptions);
        $js = "
            var _pjaxClientOptions = {$options};
        ";
        $view = $this->getView();
        $view->registerJs($js,\yii\web\View::POS_HEAD);
        PjaxAsset::register($view);
        /*
        if ($this->pjaxLinkSelector !== false) {
            $linkSelector = Json::htmlEncode($this->pjaxLinkSelector !== null ? $this->pjaxLinkSelector : '#' . $this->pjaxContainerId . ' a');
            $js .= "jQuery(document).pjax($linkSelector, $options);";
        }
        */
    }

    /**
     * Renders the filter.
     * @return string the rendering result.
     */
    public function renderFiltersXle()
    {
        $r=1;
        if (isset($this->filterView) && isset($this->dataProvider->filterModel)){
            $filter = $this->dataProvider->filterModel;
            $filterButton = Html::button('<span class="glyphicon glyphicon-chevron-down"></span>', [
              //  'title' => \Yii::t('app', 'Фільтр'),
                'onclick' => 'buttonFilterShow(this);',
                'class' => 'show-filter-btn',
            ]);
            $uploadButton = Html::button('<span class="glyphicon glyphicon-floppy-save"></span>', [
                'title' => 'В файл',
                'onclick' => 'startBackgroundUploadTask();',
                'class' => 'show-filter-btn',

                //  'id' => 'uploadStartBtn',
            ]);

            if (is_array($this->dataProvider->filterModel->filterContent)) {
                $filterContent = implode(',', $this->dataProvider->filterModel->filterContent);
            } else {
                $filterContent = $this->dataProvider->filterModel->filterContent;
            }
            if ($this->useCheckForRows) {
                $actionsWithChecked = '';
                if (isset($this->checkActionList['actions']) && isset($this->checkActionList['options'])) {
                    $actionsWithChecked = "
                           <select class='checkActionsSelect' onchange='" . $this->checkActionList['options']['onchange'] . "'>
                                <option disabled selected value='label'>Операции с выбранными строками</option>" . PHP_EOL;
                    foreach ($this->checkActionList['actions'] as $action => $text) {
                        $actionsWithChecked .= "<option value='$action'>$text</option>" . PHP_EOL ;
                    }
                    $actionsWithChecked .= "</select>" . PHP_EOL;
/*
                    $actionsWithChecked_ = Html::dropDownList(null,
                        null,$this->checkActionList['actions'], $this->checkActionList['options']);
*/
                }
                $filterBody = '
            <tr>
                <td>
                   <div class="row">
                        <div class="col-lg-3" align="left">
                            <span>' . $actionsWithChecked . ' ' . $uploadButton . '</span>
                        </div>
                        <div class="col-md-7" align="left" style="font-style: italic;">
                             <b>' . $this->gridTitle .  '</b>'
                    . ' '
                    . $filterContent . ' ' .
                    '</div>
                        <div class="col-md-1" align="right">
                          ' . $filterButton . '
                        </div>
                   </div>
                   <div class="row">
                     <div class="col-md-12" style="display: none" id="filterZone">
                      ' . $this->render($this->filterView, [
                        'filter' => $filter,
                        'exportQuery' => $this->dataProvider->exportQuery,
                    ]) . '
                      </div>
                    </div>
                </td>
            </tr>
            ';
            } else {
                $filterBody = '
            <tr>
                <td>
                   <div class="row">
                        <div class="col-md-11" align="left" style="font-style: italic;">
                             <b>' . $this->gridTitle .  '</b>'
                    . ' '
                    . $filterContent .
                    '</div>
                        <div class="col-md-1" align="right">
                          ' . $filterButton .  ' 
                        </div>
                   </div>
                   <div class="row">
                     <div class="col-md-12" style="display: none" id="filterZone">
                      ' . $this->render($this->filterView, [
                        'filter' => $filter,
                        'exportQuery' => $this->dataProvider->exportQuery,
                    ]) . '
                      </div>
                    </div>
                </td>
            </tr>
            ';
            }

        } else {
            $filterBody ='
            <tr>
                 <td>
                     <div class="row">
                         <div class="col-md-6">
                           <b>' . $this->gridTitle .  '</b>
                         </div>
                     </div>
                </td>
            </tr>
        ';

        }
        return $filterBody;
    }

    /**
     * Renders the table body.
     * @return string the rendering result.
     */
    public function renderTableBody()
    {
        $models = array_values($this->dataProvider->getModels());
        $keys = $this->dataProvider->getKeys();
        $rows = [];
        foreach ($models as $index => $model) {
            $key = $keys[$index];
            if ($this->beforeRow !== null) {
                $row = call_user_func($this->beforeRow, $model, $key, $index, $this);
                if (!empty($row)) {
                    $rows[] = $row;
                }
            }
            $rows[] = $this->renderTableRow($model, $key, $index);

            if ($this->afterRow !== null) {
                $row = call_user_func($this->afterRow, $model, $key, $index, $this);
                if (!empty($row)) {
                    $rows[] = $row;
                }
            }
        }
        //-- TODO new
        $cJSON = \Yii::$app->conservation
            ->setConserveGridDB($this->dataProvider->conserveName, $this->dataProvider->pagination->pageParam, $this->dataProvider->pagination->getPage());
        $cJSON = \Yii::$app->conservation
            ->setConserveGridDB($this->dataProvider->conserveName, $this->dataProvider->pagination->pageSizeParam, $this->dataProvider->pagination->getPageSize());
        //-- TODO new
        if (empty($rows) && $this->emptyText !== false) {
            $colspan = count($this->columns);

            return "<tbody>\n<tr><td colspan=\"$colspan\">" . $this->renderEmpty() . "</td></tr>\n</tbody>";
        } else {
            return "<tbody>\n" . implode("\n", $rows) . "\n</tbody>";
        }
    }

    /**
     * Renders a table row with the given data model and key.
     * @param mixed $model the data model to be rendered
     * @param mixed $key the key associated with the data model
     * @param int $index the zero-based index of the data model among the model array returned by [[dataProvider]].
     * @return string the rendering result
     */
    public function renderTableRow($model, $key, $index)
    {
        $cells = [];
        /* @var $column Column */
        if ($this->dataProvider->searchId > 0 && $key == $this->dataProvider->searchId){
            foreach ($this->columns as $column) {
                $buf= $column->contentOptions;
                $column->contentOptions['class'] = 'blink-text';
                $cells[] = $column->renderDataCell($model, $key, $index);
                $column->contentOptions = $buf;
            }
        } else {
            foreach ($this->columns as $column) {
                if ($this->useCheckForRows) {
                    if (isset($column->options['class']) && $column->options['class'] == 'row-check'){
                        $cells[] = $this->renderRowCheckBox($key);
                    } else {
                        $cells[] = $column->renderDataCell($model, $key, $index);
                    }
                } else {
                    $cells[] = $column->renderDataCell($model, $key, $index);
                }

            }

        }

        if ($this->rowOptions instanceof Closure) {
            $options = call_user_func($this->rowOptions, $model, $key, $index, $this);
        } else {
            $options = $this->rowOptions;
        }
        $options['data-key'] = is_array($key) ? json_encode($key) : (string) $key;

        return Html::tag('tr', implode('', $cells), $options);
    }

    public function renderTableHeaderXle()
    {
        $cells = [];
        foreach ($this->columns as $column) {
            /* @var $column Column */
            $cells[] = $column->renderHeaderCell();
        }
        $content = Html::tag('tr', implode('', $cells), $this->headerRowOptions);

        return "<thead>\n" . $content . "\n</thead>";
    }

    public function renderItems()
    {
        $filter = $this->renderFiltersXle();
        $caption = $this->renderCaption();
        $columnGroup = $this->renderColumnGroup();
        $tableHeader = $this->showHeader ? $this->renderTableHeaderXle() : false;
        $tableBody = $this->renderTableBody();

        $tableFooter = false;
        $tableFooterAfterBody = false;

        if ($this->showFooter) {
            if ($this->placeFooterAfterBody) {
                $tableFooterAfterBody = $this->renderTableFooter();
            } else {
                $tableFooter = $this->renderTableFooter();
            }
        }

        $content = array_filter([
            $caption,
            $columnGroup,
            $tableHeader,
            $tableFooter,
            $tableBody,
            $tableFooterAfterBody,
        ]);
        $filterRenderOptions = [
            'class' => 'table table-bordered',
            'style' => 'background: none',
        ];
        if (isset($this->tableOptions['class'])){
            $this->filterRenderOptions['class'] = str_replace('table-striped', '', $this->tableOptions['class']);
        }
        if (isset($this->tableOptions['style'])){
            $this->filterRenderOptions['style'] .= ';' .$this->tableOptions['style'];
        }

        $ret = Html::tag('table', $filter, $filterRenderOptions)
            . Html::tag('table', implode("\n", $content), $this->tableOptions);

        return $ret;
    }

    public function renderRowCheckBox($key)
    {
        $checked = (is_array($this->checkedIds) && in_array($key, $this->checkedIds)) ? 'checked' : '';
        $checkBox = '<input type="checkbox" id="row-check-' .  $key . '" class="row-check" data-id = "' . $key . '" onChange="checkRow(this);" ' . $checked . '>';
        return Html::tag('td', $checkBox);
    }
}