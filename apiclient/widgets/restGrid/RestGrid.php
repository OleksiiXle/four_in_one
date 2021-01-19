<?php
namespace app\widgets\restGrid;

use common\models\UserM;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveFormAsset;

class RestGrid extends GridView
{
    public $filterRenderOptions = [
        'class' => 'table table-bordered',
        'style' => 'background: none',
    ];

    public function run()
    {
        $view = $this->getView();
        if (!empty($this->dataProvider->autoFilter)) {
            $js = "
            var _filterClassShortName = '" . $this->dataProvider->autoFilterModel->shortName . "';
            ";
            $view->registerJs($js,\yii\web\View::POS_HEAD);
        }

        RestGridAsset::register($view);
        ActiveFormAsset::register($view);
        parent::run();
    }

    /**
     * Renders the filter.
     * @return string the rendering result.
     */
    public function renderFilters()
    {
        $r=1;
        $filterButton = Html::button('<span class="glyphicon glyphicon-chevron-down"></span>', [
            'onclick' => 'buttonFilterShow(this);',
            'class' => 'show-filter-btn',
        ]);
        if (!empty($this->dataProvider->autoFilter)) {
            $model = $this->dataProvider->autoFilterModel;
            $colsCount = $model->colsCount;
            $attributes = $this->dataProvider->autoFilter['attributes'];
            $filterBody = '
            <tr>
                <td>
                   <div class="row">
                        <div class="col-lg-3" align="left">
                            <span></span>
                        </div>
                        <div class="col-md-7" align="left" style="font-style: italic;">'
                . '</div>
                        <div class="col-md-1" align="right">
                          ' . $filterButton . '
                        </div>
                   </div>
                   <div class="row">
                     <div class="col-md-12" style="display: none" id="filterZone">
                      ' . $this->render('autoFilter', [
                          'model' => $model,
                          'colsCount' => $colsCount,
                          'attributes' => $attributes,
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
     * Renders the table header.
     * @return string the rendering result.
     */
    public function renderTableHeader()
    {
        $cells = [];
        foreach ($this->columns as $column) {
            /* @var $column Column */
            $cells[] = $column->renderHeaderCell();
        }
        $content = Html::tag('tr', implode('', $cells), $this->headerRowOptions);

        return "<thead>\n" . $content . "\n</thead>";
    }
}