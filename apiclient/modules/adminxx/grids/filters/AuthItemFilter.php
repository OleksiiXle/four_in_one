<?php

namespace app\modules\adminxx\grids\filters;

use Yii;
use app\modules\adminxx\models\AuthItemX;
use common\widgets\xgrid\models\GridFilter;

class AuthItemFilter extends GridFilter
{
    public $queryModel = AuthItemX::class;

    public $name;
    public $type;
    public $description;
    public $rule_name;

    public function rules()
    {
        $ownRules = [
            [['type'], 'integer'],
            [['description', 'rule_name' , 'name'], 'string', 'max' => 64]
        ];

        return array_merge(parent::rules(), $ownRules);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type' => \Yii::t('app', 'Тип'),
            'name' => \Yii::t('app', 'Название'),
            'rule_name' => \Yii::t('app', 'Правило'),
            'description' => \Yii::t('app', 'Описание'),
            'showOnlyChecked' => Yii::t('app', 'Только выбранные'),
        ];
    }

    public function getCustomQuery()
    {
        $tmp = 1;
        switch ($this->type){
            case AuthItemX::TYPE_All:
                $query = AuthItemX::find();
                break;
            case AuthItemX::TYPE_ROLE:
                $query = AuthItemX::find()
                    ->andWhere(['type' => AuthItemX::TYPE_ROLE]);
                break;
            case AuthItemX::TYPE_PERMISSION:
                $query = AuthItemX::find()
                    ->andWhere(['type' => AuthItemX::TYPE_PERMISSION])
                    ->andWhere('NOT (name LIKE "/%")');
                break;
            case AuthItemX::TYPE_ROUTE:
                $query = AuthItemX::find()
                    ->andWhere(['type' => AuthItemX::TYPE_PERMISSION])
                    ->andWhere('name LIKE "/%"');
                break;
            default:
                $query = AuthItemX::find();

        }

        return $query;
    }

    public function getQuery()
    {
        $query = $this->defaultQuery;

        if (!$this->validate()) {
            return $query;
        }

        if (!empty($this->name)) {
            $query->andWhere(['like', 'name', $this->name]);
            $this->_filterContent .= Yii::t('app', 'Название') . '"' . $this->name . '"; ' ;
        }

        if (!empty($this->rule_name) && $this->rule_name != 'Без правила') {
            $query->andWhere(['like', 'rule_name', $this->rule_name]);
            $this->_filterContent .= Yii::t('app', 'Правило') . '"' . $this->rule_name . '"; ' ;
        }

        return $query;
    }
}