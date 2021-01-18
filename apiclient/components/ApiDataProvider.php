<?php

namespace app\components;

use Yii;
use yii\data\BaseDataProvider;
//use app\components\Sort;
use \yii\data\Sort;
/** throws */
use yii\base\InvalidParamException;

class ApiDataProvider extends BaseDataProvider {

    public $link          = null;
    public $apiLinkParams = [];
    public $apiBodyParams = null;
    public $apiMethod     = 'GET';

    /**
     * @var string|callable the column that is used as the key of the data models.
     * This can be either a column name, or a callable that returns the key value of a given data model.
     * If this is not set, the index of the [[models]] array will be used.
     * @see getKeys()
     */
    public $key;

    /**
     * @var string the name of the [[\yii\base\Model|Model]] class that will be represented.
     * This property is used to get columns' names.
     */
    public $modelClass;

    protected $params = null;
    protected $_sort;
    private $_queryParams = null;

    /**
     * @var \yii\base\Model
     */
    public $filterModel;

    /**
     * @var \UAConserve
     */
    public $conserve;

    /**
     * @return mixed
     */
    public function getXapi()
    {
        if ($this->_xapi === null) {
            $this->_xapi = Yii::$app->xapi;
        }
        return $this->_xapi;
    }

    /**
     * @return mixed
     */
    public function getQueryParams()
    {
        if ($this->_queryParams === null) {
            $this->_queryParams = Yii::$app->request->getQueryParams();
        }
        return $this->_queryParams;
    }
    public $conservePart = 'grids';
    public $conserveName = '';
    
    private $_xapi = null;
    public $xapiParams = [];

    public function init() {
        parent::init();

        try {

            $queryParams = $this->queryParams;
            /*
            $this->pagination = ($this->usePagination)
                ? [
                    'class'            => 'common\components\conservation\PaginationConserve',
                    'conserveName' => $this->conserveName,
                    'pageSize' => $this->pageSize,
                    'startPage' => $this->conserves['startPage'],
                    'totalCount' => 0,
                    'searchId' => $this->searchId,
                ]
                : false;
            */

            if ($this->pagination) {
                if (isset($this->queryParams[$this->pagination->pageParam]) &&
                    isset($this->queryParams[$this->pagination->pageSizeParam])) {
                    $this->pagination->page = $this->queryParams[$this->pagination->pageParam];
                } else {
                    $this->pagination->page = 1;
                }
                $this->apiLinkParams[$this->pagination->pageParam] = $this->pagination->page;
                $this->apiLinkParams[$this->pagination->pageSizeParam] = $this->pagination->pageSize;
            }

            $sort = $this->getSort();
            if ($sort && isset($this->queryParams[$sort->sortParam])) {
                $this->apiLinkParams[$sort->sortParam] = $this->queryParams[$sort->sortParam];
            }

            if (($filter = $this->getFilter()) !== false) {
                foreach ($filter as $key => $value) {
                    $this->apiLinkParams["filter"][$key] = $value;
                }
            }


        } catch (\Exception $ex) {

        }
        if (!empty($params)) {
            $this->api->setHandlerParams($params);
        }
        return true;
        /*

        if (trim($this->conserveName) == "") {
            $this->conserveName = $this->modelClass;
        }
        if (empty($this->conserveName)) {
            return true;
        }
        $this->conserve = Yii::$app->uac->data->getConserve($this->conservePart,
            $this->conserveName);
        if (($sort           = $this->getSort()) !== false) {
            $sort->setAttributeOrders($this->conserve ? $this->conserve->get("sort.orders",
                [], false) : []);
            $sort->defaultOrder = $this->conserve ? $this->conserve->get("sort.orders",
                [], false) : [];
        }
        if (($this->filterModel !== false) && !is_null($this->filterModel)) {
            if (is_string($this->filterModel)) {
                $this->filterModel = Yii::createObject($this->filterModel);
            }
            if ($this->filterModel instanceof \yii\base\Model) {
                if ($this->conserve) {
                    $this->filterModel->setAttributes($this->conserve->get("filter.filters"));
                } else {
                    $this->filterModel->load(\Yii::$app->request->post());
                }
            }
        }
        */
    }

    /**
     * Prepares the data models that will be made available in the current page.
     * @return array the available data models
     */
    protected function prepareModels()
    {
        $result = $this->xapi
            ->callMethod($this->link, $this->apiLinkParams, $this->apiMethod,
                $this->apiBodyParams);

        $this->models = [];
        if ($result['returnStatus'] == XapiV1Client::RETURN_SUCCESS) {
            $this->models = $result['data'];

            $headerTotalCount = $result['headers']
                ->get('x-pagination-total-count', 0);

            $this->totalCount = $headerTotalCount;
            if (is_object($this->pagination)) {
                $this->pagination->totalCount = $headerTotalCount;
                $this->pagination->page       = $result['headers']
                        ->get('x-pagination-current-page', 0) - 1;
                $this->pagination->pageSize   = $result['headers']
                    ->get('x-pagination-per-page', 0);
            }
        }

        return $this->models;
    }

    /**
     * Prepares the keys associated with the currently available data models.
     * @param array $models the available data models
     * @return array the keys
     */
    protected function prepareKeys($models) {
        if ($this->key !== null) {
            $keys = [];
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }

            return $keys;
        } else {
            return array_keys($models);
        }
    }

    /**
     * Returns a value indicating the total number of data models in this data provider.
     * @return integer total number of data models in this data provider.
     */
    protected function prepareTotalCount() {
        return count($this->models);
    }

    public function getSort() {
        if ($this->_sort === null) {
            $this->setSort([]);
        }

        return $this->_sort;
    }

    /**
     *
     * @param array|Sort|bool $value
     */
    public function setSort($value)
    {
        $tmp = 1;
        if (is_array($value)) {
            $config      = ['class' => Sort::className()];
            $this->_sort = Yii::createObject(array_merge($config, $value));
            if (empty($value)) {
                /* @var $model Model */
                $model = new $this->modelClass;
                if (empty($this->_sort->attributes)) {
                    foreach ($model->attributes() as $attribute) {
                        $this->_sort->attributes[$attribute] = [
                            'asc'   => [$attribute => SORT_ASC],
                            'desc'  => [$attribute => SORT_DESC],
                            'label' => $model->getAttributeLabel($attribute),
                        ];
                    }
                }
            }
        } elseif ($value instanceof Sort || $value === false) {
            $this->_sort = $value;
        } else {
            throw new InvalidParamException('Only Sort instance, configuration array or false is allowed.');
        }
    }

    public function getFilter() {
        if (!is_null($this->filterModel) && ($this->filterModel instanceof \yii\base\Model)) {
            return $this->filterModel;
        } else {
            return false;
        }
    }
}
