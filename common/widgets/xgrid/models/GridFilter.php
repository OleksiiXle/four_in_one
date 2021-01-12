<?php

namespace common\widgets\xgrid\models;

use Yii;
use yii\base\Model;

/**
 * Class GridFilter
 * От этого класса наследуются фильтры для гридов
 *
 * @package common\widgets\xgrid\models
 */
abstract class GridFilter extends Model
{
    public $queryModel; //-- основная модель, например  UserM::class;
    public $checkedIds = []; //-- идентификаторы выбранных строк грида
    public $allRowsAreChecked =  false; //-- признак, что выбраны все строки с учетом применения условий
    public $actionWithChecked = false; //-- признак, что операция проводится с выбранными  строками
    public $showOnlyChecked; //-- показывать только выбранный строки, но с учетом наложенных условий
    public $_filterContent = null; //-- текстовая строка, говорящая о том, какие условия сейчас применены
    public $primaryKey = 'id';
    private $_sqlPrefix;

    /**
     * @var
     */
    private $_defaultQuery;

    /**
     * Основа пользовательского запроса, на который потом накладывается фильтр. Например:
     *         - return UserM::find()->joinWith(['userDatas']);
     * @return mixed
     */
    abstract public function getCustomQuery();

    /**
     * Метод для формирования фильтра
     * берется $this->defaultQuery, валидируется, накладываются условия, сразу формируется $_filterContent
     * Например:
     *      public function getQuery()
     *      {
     *          $query = $this->defaultQuery;
     *          if (!$this->validate()) {
     *              return $query;
     *          }
     *          if ($this->showStatusActive =='1'){
     *              $query->andWhere(['user.status' => UserM::STATUS_ACTIVE]);
     *              $this->_filterContent .= Yii::t('app', 'Только активные');
     *         }
     *         return $query;
     *      }
     * @return mixed
     */
    abstract public function getQuery();

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [[$this->primaryKey], 'safe'],
            [['checkedIds'], 'safe'],
            [[ 'showOnlyChecked', 'allRowsAreChecked', 'actionWithChecked'], 'boolean'],
        ];
    }

    /**
     * Геттер - берется пользовательский getCustomQuery() и,
     * с учетом значений $allRowsAreChecked, $actionWithChecked, $showOnlyChecked и $checkedIds
     * формируется  $_defaultQuery, на который потом, в getQuery() накладываются условия
     * @return mixed
     */
    public function getDefaultQuery()
    {
        $this->_defaultQuery = $this->getCustomQuery();
        if (!$this->actionWithChecked) {
            if (!$this->allRowsAreChecked && $this->showOnlyChecked == '1' && !empty($this->checkedIds)) {
                $this->_defaultQuery
                    ->andWhere(['IN', "$this->sqlPrefix.$this->primaryKey", $this->checkedIds]);
                $this->_filterContent .= Yii::t('app', 'Только отмеченные');
            }
        } else {
            if (!$this->allRowsAreChecked && !empty($this->checkedIds)) {
                $this->_defaultQuery
                    ->andWhere(['IN', "$this->sqlPrefix.$this->primaryKey", $this->checkedIds]);
            }
        }

     //   $tmp = $this->_defaultQuery->createCommand()->getSql();
        return $this->_defaultQuery;
    }

    /**
     * @return null|string
     */
    public function getFilterContent()
    {
        $this->_filterContent = '';
        $this->getQuery();

        return $this->_filterContent;
    }

    /**
     * @return mixed
     */
    public function getSqlPrefix()
    {
        if ($this->_sqlPrefix === null) {
            $this->_sqlPrefix = ($this->queryModel)::tableName();
        }
        return $this->_sqlPrefix;
    }


    /**
     * Определение столбцов для вывода в файл
     * @return array
     */
    public function getDataForUpload()
    {
        //-- пример:
        return [
            'attributeName' => [  //-- в столбец выводится значение attributeName с подписью сверху label
                'label' => 'Attribute label',
                'content' => 'value'
            ],
            'callBack' => [ //-- в столбец выводится значение function с подписью сверху label
                'label' => 'Call back label',
                'content' => function($model)
                {
                    return ($model->id == 1) ? 'id = 1' : 'id != 1';
                }
            ],
        ];
    }
}