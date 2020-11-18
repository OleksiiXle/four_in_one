<?php

namespace common\components\conservation;

use Yii;
use yii\data\Pagination;
use yii\web\Request;

class PaginationConserve extends Pagination
{
    public $startPage = 1;
    public $conserveName;
    public $searchId = 0;
    public $searchPage = 0;


    /**
     * Returns the value of the specified query parameter.
     * This method returns the named parameter value from [[params]]. Null is returned if the value does not exist.
     * @param string $name the parameter name
     * @param string $defaultValue the value to be returned when the specified parameter does not exist in [[params]].
     * @return string the parameter value
     */
    protected function getQueryParam($name, $defaultValue = null)
    {
        $tmp=1;
        if (($params = $this->params) === null) {
            $request = Yii::$app->getRequest();
            if ($name == $this->pageParam){
                $buf = $request->getQueryParams();
                if (!isset($buf[$name])){
                    $params[$name] = $this->startPage + 1;
                } else {
                    $params = $request instanceof Request ? $request->getQueryParams() : [];
                }
                if ($this->searchId > 0){
                    $params[$name] = $this->searchPage;
                }
            } else {
                $params = $request instanceof Request ? $request->getQueryParams() : [];
            }
        }
        $result = isset($params[$name]) && is_scalar($params[$name]) ? $params[$name] : $defaultValue;

        return $result;
    }

    /**
     * @return int the offset of the data. This may be used to set the
     * OFFSET value for a SQL statement for fetching the current page of data.
     */
    public function getOffset()
    {
        $pageSize = $this->getPageSize();

        return $pageSize < 1 ? 0 : $this->getPage() * $pageSize;
    }
}
