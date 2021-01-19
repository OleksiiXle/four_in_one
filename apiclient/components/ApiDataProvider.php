<?php

namespace app\components;

use app\widgets\restGrid\models\AutoFilter;
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
    /*
          'autoFilter' => [
                'attributes' => [                       - список аттрибутов для фильтра
                    'name' => [
                        'col' => 1,                     - в какой колонки вьюхи выводить поле (до 4-х)
                        'label' => 'Название',          - подпись
                        'condition' => 'LIKE',          - условие, потом обрабатывается, как $query->andWhere([$params['condition'], $attribute, $params['value']]);
                        'renderType' => 'input',        - тип поля для вьюхи (пока только input, dropdownList)
                    ],
                    'id' => [
                        'col' => 2,
                        'condition' => '=',
                        'renderType' => 'input',
                    ],
                    'type' => [
                        'col' => 2,
                        'condition' => '=',
                        'renderType' => 'dropdownList',
                        'list' => [                     - для dropdownList - список возможных значений
                            0 => 'Все',
                            1 => 'Главная страница',
                            2 => 'Привязка к цели',
                        ],
                    ],
                ],
                'rules' => [                            - правила валидации для $_autoFilterModel
                    [['id', 'type'], 'integer'],
                    [['name'], 'string', 'min' => 3, 'max' =>20],
                    [['name'], 'match', 'pattern' => AutoFilter::PATTERN_TEXT, 'message' => AutoFilter::PATTERN_TEXT_ERROR_MESSAGE,],
                ],
            ],
     */
    public $autoFilter = [];
    /*
     * Модель авто-фильтра, которая динамически создается с использованием $autoFilter
     * @var app\widgets\restGrid\models\AutoFilter
     */
    private $_autoFilterModel = null;

    /**
     * @var \yii\base\Model
     */
    public $filterModel;

    private $_xapi = null;
    public $xapiParams = [];

    //---------------------------------------------------------------------------------- ГЕТТЕРЫ
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

    /**
     * @return mixed
     */
    public function getAutoFilterModel()
    {
        if ($this->_autoFilterModel === null) {
            $this->_autoFilterModel = new AutoFilter();
            if (!empty($this->autoFilter)) {
                $this->_autoFilterModel->prepare($this->autoFilter);
            }
        }
        return $this->_autoFilterModel;
    }

    //----------------------------------------------------------------------------------

    public function init() {
        parent::init();
        $tmp = $this->autoFilterModel;

        try {

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

            if (!empty($this->autoFilter) && isset($this->queryParams['filter'])) {
                $filterArray = json_decode($this->queryParams['filter'], true);
                $filter = $this->autoFilterModel->getFilter($filterArray);
                foreach ($filter as $key => $value) {
                    //    $this->apiLinkParams["filter"][$key] = $value;
                    $this->apiBodyParams["filter"][$key] = $value;
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getTraceAsString());

        }
        if (!empty($params)) {
            $this->api->setHandlerParams($params);
        }
        return true;
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

}
