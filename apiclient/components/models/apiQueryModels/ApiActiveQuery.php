<?php

namespace app\components\models\apiQueryModels;

use yii\db\ActiveQuery;

class ApiActiveQuery extends ActiveQuery
{
    /**
     * Creates a DB command that can be used to execute this query.
     * @param Connection|null $db the DB connection used to create the DB command.
     * If `null`, the DB connection returned by [[modelClass]] will be used.
     * @return Command the created DB command instance.
     */
    public function createCommand()
    {
        /* @var $modelClass ApiActiveRecord */
        $modelClass = $this->modelClass;
/*
        if ($this->sql === null) {
            list($sql, $params) = $this->createQueryBuilder()->build($this);
        } else {
            $sql = $this->sql;
            $params = $this->params;
        }
*/

        $command = \Yii::createObject(ApiCommand::class);
        $command->query = $this;

//        $command = $db->createCommand($sql, $params);
        $this->setCommandCache($command);

        return $command;
    }

    public function createQueryBuilder()
    {
        return new ApiQueryBuilder();
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