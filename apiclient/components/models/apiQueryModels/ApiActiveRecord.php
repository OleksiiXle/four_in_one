<?php

namespace app\components\models\apiQueryModels;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class ApiActiveRecord extends ActiveRecord
{
    /**
     * @return object|\yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public static function find()
    {
        return \Yii::createObject(ApiActiveQuery::class, [get_called_class()]);
    }

    /**
     * Populates an active record object using a row of data from the database/storage.
     *
     * This is an internal method meant to be called to create active record objects after
     * fetching data from the database. It is mainly used by [[ActiveQuery]] to populate
     * the query results into active records.
     *
     * When calling this method manually you should call [[afterFind()]] on the created
     * record to trigger the [[EVENT_AFTER_FIND|afterFind Event]].
     *
     * @param BaseActiveRecord $record the record to be populated. In most cases this will be an instance
     * created by [[instantiate()]] beforehand.
     * @param array $row attribute values (name => value)
     */
    public static function populateRecord($record, $row)
    {
        $tmp = 1;
       // $columns = array_flip($record->attributes());
        $attributes = array_keys($record->getAttributes());
        foreach ($row as $name => $value) {
            if (in_array($name, $attributes)) {
              //  $record->_attributes[$name] = $value;
                $record->$name = $value;
            }
        }
      //  $record->_oldAttributes = $record->_attributes;
      //  $record->_related = [];
     //   $record->_relationsDependencies = [];
    }


    /**
     * Returns the schema information of the DB table associated with this AR class.
     * @return TableSchema the schema information of the DB table associated with this AR class.
     * @throws InvalidConfigException if the table for the AR class does not exist.
     */
    public static function getTableSchema__()
    {
        $tableSchema = static::getDb()
            ->getSchema()
            ->getTableSchema(static::tableName());

        if ($tableSchema === null) {
            throw new InvalidConfigException('The table does not exist: ' . static::tableName());
        }

        return $tableSchema;
    }

    /**
     * Returns the list of all attribute names of the model.
     * The default implementation will return all column names of the table associated with this AR class.
     * @return array list of attribute names.
     */
    public function attributes()
    {
        return array_keys($this->getAttributes());
    }

    /**
     * Finds ActiveRecord instance(s) by the given condition.
     * This method is internally called by [[findOne()]] and [[findAll()]].
     * @param mixed $condition please refer to [[findOne()]] for the explanation of this parameter
     * @return ActiveQueryInterface the newly created [[ActiveQueryInterface|ActiveQuery]] instance.
     * @throws InvalidConfigException if there is no primary key defined.
     * @internal
     */
    protected static function findByCondition($condition)
    {
        $query = static::find();

        if (!ArrayHelper::isAssociative($condition) && !$condition instanceof ExpressionInterface) {
            // query by primary key
            $primaryKey = static::primaryKey();
            if (isset($primaryKey[0])) {
                $pk = $primaryKey[0];
                if (!empty($query->join) || !empty($query->joinWith)) {
                    $pk = static::tableName() . '.' . $pk;
                }
                // if condition is scalar, search for a single primary key, if it is array, search for multiple primary key values
                $condition = [$pk => is_array($condition) ? array_values($condition) : $condition];
            } else {
                throw new InvalidConfigException('"' . get_called_class() . '" must have a primary key.');
            }
        } elseif (is_array($condition)) {
            $aliases = static::filterValidAliases($query);
            $condition = static::filterCondition($condition, $aliases);
        }

        return $query->andWhere($condition);
    }


    /**
     * Filters array condition before it is assiged to a Query filter.
     *
     * This method will ensure that an array condition only filters on existing table columns.
     *
     * @param array $condition condition to filter.
     * @param array $aliases
     * @return array filtered condition.
     * @throws InvalidArgumentException in case array contains unsafe values.
     * @throws InvalidConfigException
     * @since 2.0.15
     * @internal
     */
    protected static function filterCondition(array $condition, array $aliases = [])
    {
        $result = [];
      //  $db = static::getDb();
     //   $columnNames = static::filterValidColumnNames($db, $aliases);

        foreach ($condition as $key => $value) {
           // if (is_string($key) && !in_array($db->quoteSql($key), $columnNames, true)) {
          //      throw new InvalidArgumentException('Key "' . $key . '" is not a column name and can not be used as a filter');
          //  }
            $result[$key] = is_array($value) ? array_values($value) : $value;
        }

        return $result;
    }



}