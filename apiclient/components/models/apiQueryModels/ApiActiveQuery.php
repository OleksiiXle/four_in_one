<?php

namespace app\components\models\apiQueryModels;

use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class ApiActiveQuery extends ActiveQuery
{
    public $fields = false; // = ['*'];
    public $extraFields = false; //= [];

    /**
     * Если заданы $this->fields или $this->extraFields, то не зависимо от значения $this->asArray
     * вернется массив, полученный с помощью ->toArray($this->fields, $this->extraFields)
     * для каждой единицы результата
     * @param array $fields
     * @return $this
     */
    public function requiredFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param array $extraFields
     * @return $this
     */
    public function requiredExtraFields($extraFields)
    {
        $this->extraFields = $extraFields;
        return $this;
    }

    /**
     * Creates a API command that can be used to execute this query.
     * @param null $db the DB connection used to create the DB command.
     * If `null`, the DB connection returned by [[modelClass]] will be used.
     * @return ApiCommand the created DB command instance.
     */
    public function createCommand($db = NULL)
    {
        /* @var $modelClass ApiActiveRecord */
        $command = \Yii::createObject(ApiCommand::class);
        $buff = explode('\\', $this->modelClass);
        $command->modelName = $buff[count($buff) -1 ];
        $command->query = $this;
        $this->setCommandCache($command);

        return $command;
    }

    public function createQueryBuilder()
    {
       // return new ApiQueryBuilder();
        return false;
    }

    public function populate($rows)
    {
        if (empty($rows)) {
            return [];
        }

        $models = $this->createModels($rows);
        if (!empty($this->join) && $this->indexBy === null) {
           // $models = $this->removeDuplicatedModels($models);
        }
        if (!empty($this->with)) {
            $this->findWith($this->with, $models);
        }

        if (!$this->asArray) {
            foreach ($models as $model) {
                $model->afterFind();
            }
        }
        if ($this->indexBy === null) {
            return $models;
        }
        $result = [];
        foreach ($models as $model) {
            $result[ArrayHelper::getValue($model, $this->indexBy)] = $model;
        }

        return $result;

        return parent::populate($models);
    }

    /**
     * {@inheritdoc}
     */
    protected function queryScalar($selectExpression, $db)
    {
        /* @var $modelClass ApiActiveRecord */
        $command = \Yii::createObject(ApiCommand::class);
        $buff = explode('\\', $this->modelClass);
        $command->modelName = $buff[count($buff) -1 ];
        $command->query = $this;
        $command->selectExpression = $selectExpression;

        return $command->queryScalar();


        if ($db === null) {
            $db = $modelClass::getDb();
        }

        if ($this->sql === null) {
            return parent::queryScalar($selectExpression, $db);
        }

        $command = (new Query())->select([$selectExpression])
            ->from(['c' => "({$this->sql})"])
            ->params($this->params)
            ->createCommand($db);
        $this->setCommandCache($command);

        return $command->queryScalar();
    }

    /**
     * todo не переписано Removes duplicated models by checking their primary key values.
     * This method is mainly called when a join query is performed, which may cause duplicated rows being returned.
     * @param array $models the models to be checked
     * @throws InvalidConfigException if model primary key is empty
     * @return array the distinctive models
     */
    private function removeDuplicatedModels($models)
    {
        $hash = [];
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pks = $class::primaryKey();

        if (count($pks) > 1) {
            // composite primary key
            foreach ($models as $i => $model) {
                $key = [];
                foreach ($pks as $pk) {
                    if (!isset($model[$pk])) {
                        // do not continue if the primary key is not part of the result set
                        break 2;
                    }
                    $key[] = $model[$pk];
                }
                $key = serialize($key);
                if (isset($hash[$key])) {
                    unset($models[$i]);
                } else {
                    $hash[$key] = true;
                }
            }
        } elseif (empty($pks)) {
            throw new InvalidConfigException("Primary key of '{$class}' can not be empty.");
        } else {
            // single column primary key
            $pk = reset($pks);
            foreach ($models as $i => $model) {
                if (!isset($model[$pk])) {
                    // do not continue if the primary key is not part of the result set
                    break;
                }
                $key = $model[$pk];
                if (isset($hash[$key])) {
                    unset($models[$i]);
                } elseif ($key !== null) {
                    $hash[$key] = true;
                }
            }
        }

        return array_values($models);
    }
}