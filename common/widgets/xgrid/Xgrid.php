<?php
namespace common\widgets\xgrid;

use Yii;
use common\assets\BackgroundTaskAsset;
use common\widgets\xgrid\models\GridUploadWorker;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\grid\GridViewAsset;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Json;
use common\widgets\xgrid\models\LinkPager;

/**
 * Class Xgrid
 * Грид с перезагрузкой таблицы аяксом, фильтром, выводом в файл и пр.
 *
 * @package common\widgets\xgrid
 */
class Xgrid extends GridView
{
    //-- дефолтные значения для грида (можно менять)
    public $pager = [
        'firstPageLabel' => '<<<',
        'lastPageLabel'  => '>>>'
        ];
    public $tableOptions= [
        'class' => 'table table-bordered table-hover table-condensed',
        'style' => ' width: 100%; table-layout: fixed;',
        ];

    public $name; //** уникальное, для действия контроллера имя грида, по которому при перезагрузке определяется, какой грид обновлять
    public $renderMode; //служебное поле, определяющее режим вывода (первая загрузка, релоад, вывод в файл)
    public $filterView;// вьюха для фильтра '@app/views/dictionary/_search';
    public $filterRenderOptions = [
        'class' => 'table table-bordered',
        'style' => 'background: none',
    ];
    public $primaryKey = 'id'; //-- ключевое поле для определения выделенных строк, если не id - надо указывать
    public $useAjax = false;
    public $useActions = false; // добавлять в грид список действий с данными грида (для включения необходимо прописать действия в классе грида)
    public $actionsList = []; //список действий (формируется автоматически, задавать не надо
    public $checkedIds = [];
    public $useCustomUploadFunction = true;
    public $gridModel;
    public $assetsToRegister = [];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->filterPosition = self::FILTER_POS_HEADER;
        if ($this->useActions) {
            $this->useAjax = true;
        }
        parent::init();
    }

    /**
     * @return array|false|null|string|string[]|void
     */
    public function run()
    {
        $view = $this->getView();
  //      $this->renderMode = 'upload';
        if ($this->renderMode == 'draw') {
            $js = "
            const GRID_NAME = '$this->name';
            const GRID_ID = '$this->id';
            const USE_AJAX = '$this->useAjax';
            const PRIMARY_KEY = '$this->primaryKey';
            const USE_CUSTOM_UPLOAD_FUNCTION = '$this->useCustomUploadFunction';
            var _filterClassShortName = '" . $this->dataProvider->filterClassShortName . "';
            var _checkedIdsFromRequest = ". json_encode($this->dataProvider->filterModel->checkedIds) . ";
            var _gridModel = '". addcslashes($this->gridModel, '\\') . "';";
            if (!empty($this->dataProvider->filterModel)){
                $this->checkedIds = $this->dataProvider->filterModel->checkedIds;
                $js .= PHP_EOL . "var _filterModel = '" . addcslashes($this->dataProvider->filterModelClass, '\\') . "';";
                $js .= PHP_EOL . "var _workerClass = '" . addcslashes(GridUploadWorker::class, '\\') . "';";
            }
            $view->registerJs($js,\yii\web\View::POS_HEAD);
            XgridAsset::register($view);
            BackgroundTaskAsset::register($view);
            GridViewAsset::register($view);
            $id = $this->options['id'];
            $options = Json::htmlEncode(array_merge($this->getClientOptions(), ['filterOnFocusOut' => $this->filterOnFocusOut]));
            $view->registerJs("jQuery('#$id').yiiGridView($options);");
            foreach ($this->assetsToRegister as $assetToRegister) {
                ($assetToRegister)::register($view);
            }
        } else {
            if (!empty($this->dataProvider->filterModel)){
                $this->checkedIds = $this->dataProvider->filterModel->checkedIds;
            }
        }

        switch ($this->renderMode) {
            case 'draw':
            case 'reload':
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
                break;
            case 'upload':
                $content = $this->renderForUpload();
                break;
        }
        switch ($this->renderMode) {
            case 'draw':
                echo Html::tag($tag, $content, $options);
                break;
            case 'reload':
                $response = [
                    'body'  => Html::tag($tag, $content, $options),
                    'checkedIds' => $this->checkedIds
                ];
                return json_encode($response);
                break;
            case 'upload':
                return $content;
                break;
            default:
                return "Bad renderMode";
        }
    }

    /**
     * @return array
     */
    public function renderForUpload()
    {
        //--header
        $result = [];
        $result[] = $this->renderHeadersForUpload();

        //-- data
        $models = array_values($this->dataProvider->getModels());
        $keys = $this->dataProvider->getKeys();
        foreach ($models as $index => $model) {
            $key = $keys[$index];
            $row = [];
            foreach ($this->columns as $column) {
                if (($column instanceof SerialColumn) || ($column instanceof DataColumn && $column->label !== '')) {
                  //  $row[] = strip_tags($column->renderDataCell($model, $key, $index));
                    if ($column instanceof SerialColumn) {
                        $row[] = $index + 1;
                    } elseif ($column instanceof DataColumn && $column->content === null) {
                        $row[] = Html::encode($column->getDataCellValue($model, $key, $index));
                    } elseif ($column instanceof DataColumn) {
                        $row[] = strip_tags(call_user_func($column->content, $model, $key, $index, $this));
                    } else {
                        $row[] = '';
                    }
                }
            }
            $result[] = $row;
        }

        return $result;
    }

    /**
     * @return array
     */
    private function renderHeadersForUpload()
    {
        $row = [];
        $modelClass = $this->dataProvider->query->modelClass;
        $model = $modelClass::instance();
        $labels = $model->attributeLabels();
        foreach ($this->columns as $column) {
            if ($column instanceof SerialColumn) {
                $row[] = 'N';
            }
            if ($column instanceof DataColumn && $column->label !== '') {
                if ($column->label !== null) {
                    $row[] = Html::encode($column->label);
                } elseif ($column->attribute !== null && isset($labels[$column->attribute])) {
                    $row[] = $labels[$column->attribute];
                } elseif ($column->attribute !== null) {
                    $row[] = Inflector::camel2words($column->attribute);
                } else {
                    $row[] = '';
                }
            }
        }

        return $row;
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
                'onclick' => 'buttonFilterShow(this);',
                'class' => 'show-filter-btn',
            ]);
            if ($this->useActions) {
                $actionsWithChecked = "
                           <select class='checkActionsSelect' onchange='actionWithChecked(this);'>
                                <option disabled selected value='label'>" .  Yii::t('app', 'Действия с отмеченными строками') ."</option>" . PHP_EOL;
                foreach ($this->actionsList as $keyAction => $text) {
                    $actionsWithChecked .= "<option value='$keyAction'>$text</option>" . PHP_EOL ;
                }
                $actionsWithChecked .= "</select>" . PHP_EOL;
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
        $cJSON = \Yii::$app->conservation
            ->setConserveGridDB($this->dataProvider->conserveName, $this->dataProvider->pagination->pageParam, $this->dataProvider->pagination->getPage());
        $cJSON = \Yii::$app->conservation
            ->setConserveGridDB($this->dataProvider->conserveName, $this->dataProvider->pagination->pageSizeParam, $this->dataProvider->pagination->getPageSize());
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
        if ($this->rowOptions instanceof \Closure) {
            $options = call_user_func($this->rowOptions, $model, $key, $index, $this);
        } else {
            $options = $this->rowOptions;
        }
        $options['data-key'] = is_array($key) ? json_encode($key) : (string) $key;

        return Html::tag('tr', implode('', $cells), $options);
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
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

    /**
     * @param $key
     * @return string
     */
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
            $filterModelFilterContent = $this->dataProvider->filterModel->filterContent;
            if (is_array($filterModelFilterContent)) {
                $filterContent = implode(',', $filterModelFilterContent);
            } else {
                $filterContent = $filterModelFilterContent;
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