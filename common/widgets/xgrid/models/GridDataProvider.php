<?php

namespace common\widgets\xgrid\models;

use yii\base\InvalidConfigException;
use yii\data\BaseDataProvider;
use yii\db\QueryInterface;
use yii\base\Model;
use yii\db\ActiveQueryInterface;
use yii\db\Connection;
use yii\di\Instance;

class GridDataProvider extends BaseDataProvider
{
    public $conserveName;
    public $conserves = [];
    public $pageSize = 10;
    public $startPage =1;
    public $baseModel; //-- объект Query основной модели данных - если нет фильтра, его надо указывать
    public $filterModelClass; //-- класс модели фильтра
    public $filterModel; //-- модель фильтра
    public $hasFilter=false;
    public $searchId = 0; //-- если не 0 - первый раз переход на страницу где этот ид
    public $filterClassShortName = '';
    public $usePagination = true;
    public $consoleFilter = [];
    public $construct = 'web';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        switch ($this->construct) {
            case 'web':
                $this->webConstruct($config);
                break;
            case 'console':
                $this->consoleConstruct($config);
                break;
        }
    }

    private function webConstruct()
    {
        $session = \Yii::$app->session;
        if ($session->get('searchIid')){
            $this->searchId = $session->get('searchIid');
            $session->remove('searchIid');
        }

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
                'searchId' => $this->searchId,
            ]
            : false;
        //-- фильтр
        if (isset($this->filterModelClass)){
            if (!$this->filterModel){
                $this->filterModel = new $this->filterModelClass;
                $this->filterClassShortName = $this->filterModel->formName();
            }

            //-- пытаемся определить фильтр
            $params = [];
            $checkedIds = [];
            if(\Yii::$app->request->isPost) {
                $_post = \Yii::$app->request->post();
                //  if (isset($_post['checkedIds']) && $this->filterModel->hasProperty('checkedIds')) {
                //       $this->filterModel->checkedIds =  $_post['checkedIds'];
                //  }
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
            }

            $this->query = $this->filterModel->getQuery();
        } else {
            $this->query = $this->baseModel;
        }
    }

    private function consoleConstruct()
    {
        $this->pagination = false;
        //-- фильтр
        if (isset($this->filterModelClass)){
            if (!$this->filterModel){
                $this->filterModel = new $this->filterModelClass;
                $this->filterClassShortName = $this->filterModel->formName();
                $this->filterModel->setAttributes($this->consoleFilter);
            }
            $this->query = $this->filterModel->getQuery();
        } else {
            $this->query = $this->baseModel;
        }
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
        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }
        $query = clone $this->query;

        if (($sort = $this->getSort()) !== false) {
            $orders = $sort->getOrders();
            if (!empty($orders)){
                $cJSON = \Yii::$app->conservation->setConserveGridDB($this->conserveName, 'sort', $orders);
                $this->conserves['sort'] = $orders;
            } elseif (!empty($this->conserves['sort'])) {
                $orders = $this->conserves['sort'];
            }
            $query->addOrderBy($orders);
        }

        if (($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();
            if ($pagination->totalCount === 0) {
                return [];
            }

            if ($this->searchId > 0){
                $idField = (!empty($query->join) || !empty($query->joinWith)) ? $query->modelClass::tableName() . '.id' : 'id';
                $qtmp = clone $query;
                $qtmp->select = [$idField];
                $retTmp = $qtmp->createCommand()->queryAll();
                $fieldPosition = array_search(['id' => $this->searchId],$retTmp);
                if (isset($fieldPosition)){
                    if ($fieldPosition < $this->pageSize){
                        $offset = 0;
                    } elseif ($fieldPosition == $this->pageSize){
                        $offset = 1;
                    } else {
                        $buf = intdiv($fieldPosition, $this->pageSize);
                        $offset = $buf + 1;
                    }
                    $this->pagination->searchPage = $offset;
                }
            }
            $offs = $pagination->getOffset();
            $query->limit($pagination->getLimit())->offset($offs);
        }

        return $query->all($this->db);
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
     * Initializes the DB connection component.
     * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
     * @throws InvalidConfigException if [[db]] is invalid.
     */
    public function init()
    {
        parent::init();
        if (is_string($this->db)) {
            $this->db = Instance::ensure($this->db, Connection::className());
        }
    }

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
        } elseif ($this->query instanceof ActiveQueryInterface) {
            /* @var $class \yii\db\ActiveRecordInterface */
            $class = $this->query->modelClass;
            $pks = $class::primaryKey();
            if (count($pks) === 1) {
                $pk = $pks[0];
                foreach ($models as $model) {
                    $keys[] = $model[$pk];
                }
            } else {
                foreach ($models as $model) {
                    $kk = [];
                    foreach ($pks as $pk) {
                        $kk[$pk] = $model[$pk];
                    }
                    $keys[] = $kk;
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
        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }
        $query = clone $this->query;
        return (int) $query->limit(-1)->offset(-1)->orderBy([])->count('*', $this->db);
    }

    /**
     * {@inheritdoc}
     */
    public function setSort($value)
    {
        parent::setSort($value);
        if (($sort = $this->getSort()) !== false && $this->query instanceof ActiveQueryInterface) {
            /* @var $modelClass Model */
            $modelClass = $this->query->modelClass;
            $model = $modelClass::instance();
            if (empty($sort->attributes)) {
                foreach ($model->attributes() as $attribute) {
                    $sort->attributes[$attribute] = [
                        'asc' => [$attribute => SORT_ASC],
                        'desc' => [$attribute => SORT_DESC],
                        'label' => $model->getAttributeLabel($attribute),
                    ];
                }
            } else {
                foreach ($sort->attributes as $attribute => $config) {
                    if (!isset($config['label'])) {
                        $sort->attributes[$attribute]['label'] = $model->getAttributeLabel($attribute);
                    }
                }
            }
        }
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
        if (!empty($this->conserveName)) {
            $cJSON = \Yii::$app->conservation->setConserveGridDB(
                $this->conserveName,
                'filter',
                json_encode($this->filterModel->getAttributes())
            );
        }
    }

}