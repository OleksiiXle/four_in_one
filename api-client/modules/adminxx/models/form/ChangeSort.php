<?php

namespace app\modules\adminxx\models\form;

use app\modules\adminxx\models\UserM;
use Yii;
use yii\base\Model;

class ChangeSort extends Model
{
    const POSITION_SORT_OLD = 'sort';
    const POSITION_SORT_V1 = 'sort1';
    const POSITION_SORT_V2 = 'sort2';
    const POSITION_SORT_CONSERVE_NAME = 'positionSort';

    public $positionSort = self::POSITION_SORT_OLD;

    public function __construct(array $config = [])
    {
        $positionSort = \Yii::$app->conservation->getConserveDB(self::POSITION_SORT_CONSERVE_NAME);
        if (!empty($positionSort)) {
            $this->positionSort = $positionSort;
        }
        parent::__construct($config);
    }

    static $positionSorts = [
        self::POSITION_SORT_OLD => 'Сортування старе',
        self::POSITION_SORT_V1 => 'Варіант 1',
        self::POSITION_SORT_V2 => 'Варіант 2',
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['positionSort',], 'required'],
            [['positionSort',], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'positionSort' =>  'Оберить тип сортування посад',
        ];
    }

    public function setPositionSort()
    {
        \Yii::$app->conservation->setConserveDB(self::POSITION_SORT_CONSERVE_NAME, $this->positionSort);
        \Yii::$app->configs->positionSort = $this->positionSort;
        return true;
    }

    public static function getPositionSort()
    {
        $positionSort = \Yii::$app->conservation->getConserveDB(self::POSITION_SORT_CONSERVE_NAME);
        if (!empty($positionSort)) {
            return $positionSort;
        }
        return self::POSITION_SORT_OLD;

    }

}
