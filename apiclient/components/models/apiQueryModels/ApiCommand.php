<?php

namespace app\components\models\apiQueryModels;

use yii\base\Component;
use yii\web\BadRequestHttpException;

class ApiCommand extends Component
{
    //------------------------------------------------------------------------------ API
    const PATH_TO_API = '/query';
    public $query = null;
    public $modelName = null;
    public $selectExpression = null;
    public $apiClient;

    public function init()
    {
        $this->apiClient = \Yii::$app->xapi;
    }
    //------------------------------------------------------------------------------ API

    /**
     * +++ Performs the actual API query of a SQL statement.
     * @param string $method method of PDOStatement to be called
     * @param int $fetchMode the result fetch mode. Please refer to [PHP manual](https://secure.php.net/manual/en/function.PDOStatement-setFetchMode.php)
     * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
     * @return mixed the method execution result
     * @throws BadRequestHttpException if the query causes any problem
     * @since 2.0.1 this method is protected (was private before).
     */
    protected function queryInternal($method, $fetchMode = null)
    {
        $data = [
            'operation' => $method,
            'modelName' => $this->modelName,
            'params' => [
                'fetchMode' => $fetchMode
            ],
            'queryData' => $this->query,
            'selectExpression' => $this->selectExpression,
        ];

        $result = $this->apiClient->callMethod(self::PATH_TO_API, [], 'POST', $data);
        if (!$result['status']) {
            if (!is_array($result['data'])) {
                $errMsg = $result['data'];
            } else {
                unset($result['data']['stack-trace']);
                $errMsg = implode(PHP_EOL, $result['data']);
            }
            throw new BadRequestHttpException($errMsg);
        }

        return $result['data'];
    }

    /**
     * ++++ Executes the SQL statement and returns ALL rows at once.
     * @param int $fetchMode the result fetch mode. Please refer to [PHP manual](https://secure.php.net/manual/en/function.PDOStatement-setFetchMode.php)
     * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
     * @return array all rows of the query result. Each array element is an array representing a row of data.
     * An empty array is returned if the query results in nothing.
     * @throws BadRequestHttpException execution failed
     */
    public function queryAll($fetchMode = null)
    {
        return $this->queryInternal('queryAll', $fetchMode);
    }

    /**
     * +++ Executes the SQL statement and returns the first row of the result.
     * This method is best used when only the first row of result is needed for a query.
     * @param int $fetchMode the result fetch mode. Please refer to [PHP manual](https://secure.php.net/manual/en/pdostatement.setfetchmode.php)
     * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
     * @return array|false the first row (in terms of an array) of the query result. False is returned if the query
     * results in nothing.
     * @throws BadRequestHttpException execution failed
     */
    public function queryOne($fetchMode = null)
    {
        return $this->queryInternal('queryOne', $fetchMode);
    }

    /**
     * +++ Executes the SQL statement and returns the value of the first column in the first row of data.
     * This method is best used when only a single value is needed for a query.
     * @return string|null|false the value of the first column in the first row of the query result.
     * False is returned if there is no value.
     * @throws BadRequestHttpException execution failed
     */
    public function queryScalar()
    {
        return $this->queryInternal('queryScalar', 0);
    }

}
