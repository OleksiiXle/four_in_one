<?php
namespace common\widgets\xgrid;

use Yii;
use common\assets\BackgroundTaskAsset;
use common\widgets\xgrid\models\GridUploadWorker;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\grid\GridViewAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use common\widgets\xgrid\models\LinkPager;
use common\widgets\xgrid\models\LinkSorter;

class Xgrid extends GridView
{
    //-- дефолтніе значения для грида (можно менять)
    public $pager = [
        'firstPageLabel' => '<<<',
        'lastPageLabel'  => '>>>'
        ];
    public $tableOptions= [
        'class' => 'table table-bordered table-hover table-condensed',
        'style' => ' width: 100%; table-layout: fixed;',
        ];



    public $name; //** уникальное, для действия контроллера имя грида, по которому при перезагрузке определяется, какой грид обновлять
    public $reload = false; //служебное поле, сигнализирующее, выводится ли грид первый раз или обновляется
    public $filterView;// вьюха для фильтра '@app/views/dictionary/_search';
    public $filterRenderOptions = [
        'class' => 'table table-bordered',
        'style' => 'background: none',
    ];
    public $useAjax = false;
    public $useActions = false; // добавлять в грид список действий с данными грида (для включения необходимо прописать действия в классе грида)
    public $actionsList = []; //список действий (формируется автоматически, задавать не надо
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
    public $checkedIds = [];

    public function init()
    {
        $this->filterPosition = self::FILTER_POS_HEADER;
        if ($this->useActions) {
            $this->useAjax = true;
        }
        parent::init();
    }

    public function run()
    {
        $view = $this->getView();
        if (!$this->reload) {
            $js = "
            const GRID_NAME = '$this->name';
            const GRID_ID = '$this->id';
            const USE_AJAX = '$this->useAjax';
            var _filterClassShortName = '" . $this->dataProvider->filterClassShortName . "';
            var _checkedIdsFromRequest = ". json_encode($this->dataProvider->filterModel->checkedIds) . ";";
            if (!empty($this->dataProvider->filterModel)){
                $this->checkedIds = $this->dataProvider->filterModel->checkedIds;
                $js .= PHP_EOL . "var _filterModel = '" . addcslashes($this->dataProvider->filterModelClass, '\\') . "';";
                $js .= PHP_EOL . "var _workerClass = '" . addcslashes(GridUploadWorker::class, '\\') . "';";
            }
            $view->registerJs($js,\yii\web\View::POS_HEAD);
            XgridAsset::register($view);
            BackgroundTaskAsset::register($view);

            //-- gridView
            GridViewAsset::register($view);
            $id = $this->options['id'];
            $options = Json::htmlEncode(array_merge($this->getClientOptions(), ['filterOnFocusOut' => $this->filterOnFocusOut]));
            $view->registerJs("jQuery('#$id').yiiGridView($options);");

        } else {
            if (!empty($this->dataProvider->filterModel)){
                $this->checkedIds = $this->dataProvider->filterModel->checkedIds;
            }

        }

        //-- BaseListView
        if ($this->showOnEmpty || $this->dataProvider->getCount() > 0) {
            $content = preg_replace_callback('/{\\w+}/', function ($matches) {
                $content = $this->renderSection($matches[0]);

                return $content === false ? $matches[0] : $content;
            }, $this->layout);
        } else {
            $content = $this->renderEmpty();
        }

        $options = $this->options;
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        if (!$this->reload) {
            echo Html::tag($tag, $content, $options);
        } else {
            $response = [
                'body'  => Html::tag($tag, $content, $options),
                'checkedIds' => $this->checkedIds
            ];
            return json_encode($response);
            return Html::tag($tag, $content, $options);
        }
    }

    /**
     * Renders the filter.
     * @return string the rendering result.
     */
    public function renderFilters()
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

            if ($this->useActions) {
                $actionsWithChecked = "
                           <select class='checkActionsSelect' onchange='actionWithChecked(this);'>
                                <option disabled selected value='label'>" .  Yii::t('app', 'Действия с отмеченными строками') ."</option>" . PHP_EOL;
                foreach ($this->actionsList as $keyAction => $text) {
                    $actionsWithChecked .= "<option value='$keyAction'>$text</option>" . PHP_EOL ;
                }
                $actionsWithChecked .= "</select>" . PHP_EOL;
                /*
                if (isset($this->checkActionList['actions']) && isset($this->checkActionList['options'])) {
                    $actionsWithChecked = "
                           <select class='checkActionsSelect' onchange='" . $this->checkActionList['options']['onchange'] . "'>
                                <option disabled selected value='label'>Операции с выбранными строками</option>" . PHP_EOL;
                    foreach ($this->checkActionList['actions'] as $action => $text) {
                        $actionsWithChecked .= "<option value='$action'>$text</option>" . PHP_EOL ;
                    }
                    $actionsWithChecked .= "</select>" . PHP_EOL;
                }
                */
            }

            $filterBody = '
            <tr>
                <td>
                   <div class="row">
                        <div class="col-lg-3" align="left">
                            <span>' . $actionsWithChecked . '</span>
                        </div>
                        <div class="col-md-7" align="left" style="font-style: italic;">'
                     . '</div>
                        <div class="col-md-1" align="right">
                          ' . $filterButton . '
                        </div>
                   </div>
                   <div class="row">
                     <div class="col-md-12" style="display: none" id="filterZone">
                      ' . $this->render($this->filterView, [
                         'filter' => $filter,
                        ]) . '
                    </div>
                   </div>
                </td>
            </tr>
            ';

        } else {
            $filterBody ='
            <tr>
                 <td>
                     <div class="row">
                         <div class="col-md-6">
                           <b>No filter</b>
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
            $rows[] = $this->renderTableRow($model, $key, $index);
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
                if ($this->useActions) {
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

    public function renderTableHeader()
    {
        $cells = [];
        foreach ($this->columns as $column) {
            /* @var $column Column */
            if ($column instanceof DataColumn) {
                if (!empty($column->sortLinkOptions['class'])) {
                    $column->sortLinkOptions['class'] .= ' gridLink_' . $this->id;
                } else {
                    $column->sortLinkOptions['class'] = 'gridLink_' . $this->id;
                }
            }
            $cells[] = $column->renderHeaderCell();
        }
        $content = Html::tag('tr', implode('', $cells), $this->headerRowOptions);

        return "<thead>\n" . $content . "\n</thead>";
    }

    public function renderItems()
    {
        $filter = $this->renderFilters();
        $caption = $this->renderCaption();
        $columnGroup = $this->renderColumnGroup();
        $tableHeader = $this->showHeader ? $this->renderTableHeader() : false;
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

    /**
     * Renders the pager.
     * @return string the rendering result
     */
    public function renderPager()
    {
        $pagination = $this->dataProvider->getPagination();
        if ($pagination === false || $this->dataProvider->getCount() <= 0) {
            return '';
        }
        /* @var $class LinkPager */
        $pager = $this->pager;
        $class = ArrayHelper::remove($pager, 'class', LinkPager::class);
        $pager['pagination'] = $pagination;
        if (isset($pager['linkOptions']['class'])) {
            $pager['linkOptions']['class'] .= ' gridLink_' . $this->id;
        } else {
            $pager['linkOptions']['class'] = 'gridLink_' . $this->id;
        }
        $pager['view'] = $this->getView();

        return $class::widget($pager);
    }

    public function renderRowCheckBox($key)
    {
        $checked = ($this->dataProvider->filterModel->allRowsAreChecked || (is_array($this->checkedIds) && in_array($key, $this->checkedIds)))
            ? 'checked'
            : '';
        $checkBox = '<input type="checkbox" id="row-check-' .  $key . '" class="row-check" data-id = "' . $key . '" onChange="checkRow(this);" ' . $checked . '>';
        return Html::tag('td', $checkBox);
    }

    /**
     * Renders the summary text.
     */
    public function renderSummary()
    {
        $count = $this->dataProvider->getCount();

        if (isset($this->dataProvider->filterModel)) {
            if (is_array($this->dataProvider->filterModel->filterContent)) {
                $filterContent = implode(',', $this->dataProvider->filterModel->filterContent);
            } else {
                $filterContent = $this->dataProvider->filterModel->filterContent;
            }
        } else {
            $filterContent = '';
        }

        if ($count <= 0) {
            return $filterContent;
        }
        $summaryOptions = $this->summaryOptions;
        $tag = ArrayHelper::remove($summaryOptions, 'tag', 'div');
        if (($pagination = $this->dataProvider->getPagination()) !== false) {
            $totalCount = $this->dataProvider->getTotalCount();
            $begin = $pagination->getPage() * $pagination->pageSize + 1;
            $end = $begin + $count - 1;
            if ($begin > $end) {
                $begin = $end;
            }
            $page = $pagination->getPage() + 1;
            $pageCount = $pagination->pageCount;
            if (($summaryContent = $this->summary) === null) {
                $ret = Html::tag($tag, Yii::t('yii', 'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.', [
                    'begin' => $begin,
                    'end' => $end,
                    'count' => $count,
                    'totalCount' => $totalCount,
                    'page' => $page,
                    'pageCount' => $pageCount,
                ]), $summaryOptions) . ' ' . $filterContent;
                return $ret;
            }
        } else {
            $begin = $page = $pageCount = 1;
            $end = $totalCount = $count;
            if (($summaryContent = $this->summary) === null) {
                $ret = Html::tag($tag, Yii::t('yii', 'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.', [
                    'begin' => $begin,
                    'end' => $end,
                    'count' => $count,
                    'totalCount' => $totalCount,
                    'page' => $page,
                    'pageCount' => $pageCount,
                ]), $summaryOptions) . ' ' . $filterContent;
                return $ret;
            }
        }

        $ret = Yii::$app->getI18n()->format($summaryContent, [
            'begin' => $begin,
            'end' => $end,
            'count' => $count,
            'totalCount' => $totalCount,
            'page' => $page,
            'pageCount' => $pageCount,
        ], Yii::$app->language) . ' ' . $filterContent;


        return $ret;
    }

}