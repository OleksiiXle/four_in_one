<?php
namespace app\modules\adminxx\models\filters;

use app\modules\adminxx\models\AuthItemX;
use yii\base\Model;
use app\widgets\xlegrid\models\GridFilter;

class AuthItemFilter extends GridFilter
{
    public $name;
    public $type;
    public $description;
    public $rule_name;

    public function getFilterContent()
    {
        if ($this->_filterContent === null) {
            $this->getQuery();
        }

        return $this->_filterContent;
    }


    public function rules()
    {
        return [
            [['type'], 'required'],
            [['type'], 'integer'],
            [['description', 'rule_name' , 'name'], 'string', 'max' => 64]

        ];
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
        ];
    }


    public function getQuery($params = null){
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


        if (!$this->validate()) {
            return $query;
        }

        if (!empty($this->name)) {
            $query->andWhere(['like', 'name', $this->name]);
        }

        if (!empty($this->rule_name) && $this->rule_name != \Yii::t('app', 'Без правила')) {
            $query->andWhere(['like', 'rule_name', $this->rule_name]);
        }


        return $query;







    }



}