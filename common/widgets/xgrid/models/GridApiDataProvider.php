<?php

namespace common\widgets\xgrid\models;

use app\components\XapiV1Client;
use yii\base\InvalidConfigException;
use yii\data\BaseDataProvider;
use yii\db\QueryInterface;
use yii\base\Model;
use yii\db\ActiveQueryInterface;
use yii\db\Connection;
use yii\di\Instance;
use yii\web\Request;

class GridApiDataProvider extends BaseDataProvider
{
    public $conserveName;
    public $conserves = [];
    public $pageSize = 10;
    public $startPage =1;
    public $baseModel; //-- объект Query основной модели данных - если нет фильтра, его надо указывать
    public $filterModelClass; //-- класс модели фильтра
    public $filterModel; //-- модель фильтра
    public $hasFilter=false;
    public $filterClassShortName = '';
    public $usePagination = true;
    public $consoleFilter = [];
    public $construct = 'web';
    public $primaryKey = 'id';
    private $_apiData = null;
    private $_models = null;
    private $_totalCount = null;

    //-------------------------------------------------------------------------- API properties

    protected $apiClient = null;
    public $apiClientParams = [];
    public $link = null;
    protected $apiQuery = [
        'filter' => [],
        'checkedIds' => [],
        'sort' => [],
        'offset' => 0,
        'limit' => 0,
   ];
    public $apiMethod = 'POST';

    public function init() {
        parent::init();

        $this->webConstruct();
        $this->apiClient = \Yii::$app->xapi;
        if (!empty($this->apiClientParams)) {
            $this->apiClient->setHandlerParams($this->apiClientParams);
        }

    }

    private function webConstruct()
    {
        if (!empty($this->conserveName)) {
            $this->getConserves();
        }
        $this->pagination = ($this->usePagination)
            ? [
                'class'            => 'common\components\conservation\PaginationConserve',
                'conserveName' => $this->conserveName,
                'pageSize' => $this->pageSize,
                'startPage' => $this->conserves['startPage'],
                'totalCount' => 0,
            ]
            : false;
        //-- пагинация и сортировка
        $request = \Yii::$app->getRequest();
        $queryParams = $request instanceof Request ? $request->getQueryParams() : [];
        if ($this->pagination) {
            if (isset($queryParams[$this->pagination->pageParam])) {
                $this->pagination->startPage = $queryParams[$this->pagination->pageParam];
            }
            if (isset($queryParams[$this->pagination->pageSizeParam])) {
                $this->pagination->pageSize = $queryParams[$this->pagination->pageSizeParam];
            }
        }

        if (($sort = $this->getSort()) !== false) {
            $orders = $sort->getOrders();
            if (!empty($orders)){
                $cJSON = \Yii::$app->conservation->setConserveGridDB($this->conserveName, 'sort', $orders);
                $this->conserves['sort'] = $orders;
            } elseif (!empty($this->conserves['sort'])) {
                $orders = $this->conserves['sort'];
            }
            $this->apiQuery['sort'] = $orders;
        }

     //   if (isset($queryParams['sort'])) {
     //       $this->apiQuery['sort'] = $queryParams['sort'];
     //   }

        //-- фильтр
        if (isset($this->filterModelClass)){
            if (!$this->filterModel){
                $this->filterModel = new $this->filterModelClass;
                $this->filterModel->primaryKey = $this->primaryKey;
                $this->filterClassShortName = $this->filterModel->formName();
            }

            //-- пытаемся определить фильтр
            $params = [];
            $checkedIds = [];
            if(\Yii::$app->request->isPost) {
                $_post = \Yii::$app->request->post();
                if (isset($_post['checkedIds'])) {
                    $checkedIds = json_decode($_post['checkedIds'], true);
                }

                $_get = \Yii::$app->request->get();
                if (isset($_get['filter'])) {
                    $params = [
                        $this->filterModel->formName() => self::encodeFilter($_get['filter']),
                    ];
                } else {
                    $params = $_post;
                }
            }

            if (!empty($params)) {
                //-- фильтр пришел
                $this->filterModel->load($params);
                $this->filterModel->checkedIds = $checkedIds;
                $this->apiQuery['filter'] = $this->filterModel->getAttributes();
                $this->apiQuery['checkedIds'] = $checkedIds;

                if (!empty($this->conserveName)) {
                    $cJSON = \Yii::$app->conservation->setConserveGridDB(
                        $this->conserveName,
                        'filter',
                        json_encode($this->filterModel->getAttributes())
                    );
                    if ($this->pagination) {
                        $cJSON = \Yii::$app->conservation->setConserveGridDB($this->conserveName, $this->pagination->pageParam, 1);
                    }
                }
                if ($this->pagination) {
                    $this->pagination->startPage = 0;
                }
            } elseif(!empty($this->conserveName)) {
                //-- фильтр не пришел
                $params = (array) $this->conserves['filter'];
                $this->filterModel->setAttributes($params);
                $this->apiQuery['filter'] = $this->filterModel->getAttributes();

            }

           // $this->query = $this->filterModel->getQuery();
        } else {
          //  $this->query = $this->baseModel;
        }
    }

    /**
     * @return null
     */
    public function getModels()
    {
        if ( $this->_models === null) {
            $this->getApiData();
        }
        return $this->_models;
    }

    /**
     * @return null
     */
    public function getTotalCount()
    {
        if ( $this->_totalCount === null) {
            $this->getApiData();
        }
        return $this->_totalCount;
    }

    public function getConserves(){
        $buf = \Yii::$app->conservation->getConserveGridDB($this->conserveName);
        $this->conserves['startPage'] = (isset($buf['data']['page'])) ? $buf['data']['page'] : $this->startPage;
        $this->conserves['sort'] = (isset($buf['data']['sort'])) ? $buf['data']['sort'] : null;
        $this->conserves['filter'] = (isset($buf['data']['filter'])) ? json_decode($buf['data']['filter']) : null;

    }

    /**
     * {@inheritdoc}
     */
    protected function prepareModels()
    {
        return $this->models;
    }

    public function getApiData()
    {
        $tmp = 1;
        if ($this->_apiData === null) {
            if ($this->pagination) {
                $this->apiQuery['offset'] = ((int)$this->pagination->startPage - 1) * (int)$this->pagination->pageSize;
                $this->apiQuery['limit'] = $this->pagination->pageSize;
            } else {
                $this->apiQuery['offset'] = 0;
                $this->apiQuery['limit'] = 0;
            }

            $result = $this->apiClient->callMethod($this->link, [], $this->apiMethod,
                $this->apiQuery);

            $this->_apiData = [];
            $this->_models = [];
            $this->_totalCount = 0;

            if ($result['returnStatus'] == XapiV1Client::RETURN_SUCCESS) {
                $this->_apiData = $result['data'];
                $this->_models = $result['data']['queryData'];
                $this->_totalCount = (int)$result['data']['totalCount'];
                $this->pagination->totalCount = (int)$result['data']['totalCount'];
            }
        }

        return $this->_apiData;
    }

    private function encodeFilter($codedFilter)
    {
        $encodedFilter = json_decode($codedFilter, true);
        $ret = [];
        foreach ($encodedFilter as $param) {
            $ret[$param['name']] = $param['value'];
        }

        return $ret;
    }

    public function addConditionToFilter($condition)
    {
        $tmp = 1;
        if (!empty($this->filterModel)) {
            foreach ($condition as $key => $value) {
                $this->filterModel->{$key} = $value;
            }
            $tmp = $this->filterModel->getAttributes();
        }
        $this->saveFilterToConserve();
        $this->query = $this->filterModel->getQuery();
    }

    public function getFilter() {
        if (!is_null($this->filterModel) && ($this->filterModel instanceof \yii\base\Model)) {
            return $this->filterModel;
        } else {
            return false;
        }
    }




    /**
     * @var QueryInterface the query that is used to fetch data models and [[totalCount]]
     * if it is not explicitly set.
     */
    public $query;
    /**
     * @var string|callable the column that is used as the key of the data models.
     * This can be either a column name, or a callable that returns the key value of a given data model.
     *
     * If this is not set, the following rules will be used to determine the keys of the data models:
     *
     * - If [[query]] is an [[\yii\db\ActiveQuery]] instance, the primary keys of [[\yii\db\ActiveQuery::modelClass]] will be used.
     * - Otherwise, the keys of the [[models]] array will be used.
     *
     * @see getKeys()
     */
    public $key;
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * If not set, the default DB connection will be used.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db;

    /**
     * {@inheritdoc}
     */
    protected function prepareKeys($models)
    {
        $keys = [];
        if ($this->key !== null) {
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }

            return $keys;
        }

        return array_keys($models);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareTotalCount()
    {
        return count($this->models);
    }

    public function __clone()
    {
        if (is_object($this->query)) {
            $this->query = clone $this->query;
        }

        parent::__clone();
    }

    public function saveFilterToConserve()
    {
        $tmp = 1;
        if (!empty($this->conserveName)) {
            $cJSON = \Yii::$app->conservation->setConserveGridDB(
                $this->conserveName,
                'filter',
                json_encode($this->filterModel->getAttributes())
            );
        }
    }

}