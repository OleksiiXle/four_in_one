<?php

namespace app\modules\adminxx\grids\filters;

use Yii;
use app\components\models\Provider;
use common\widgets\xgrid\models\GridFilter;

class ProviderFilter extends GridFilter
{
    public $queryModel = Provider::class;

    public $name;
    public $client_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $ownRules = [
            [['name', 'client_id' ], 'string', 'max' => 32],
        ];

        return array_merge(parent::rules(), $ownRules);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Имя'),
            'client_id' => Yii::t('app', 'ИД клиента'),
        ];
    }

    public function getCustomQuery()
    {
        $query = Provider::find();

        return $query;
    }


    public function getQuery()
    {
        $query = $this->defaultQuery;

        if (!empty($this->name)){
            $query->andWhere(['LIKE', 'name', $this->name ]);
            $this->_filterContent .= Yii::t('app', 'Имя') . ' = ' . $this->name . '; ' ;
        }

        if (!empty($this->client_id)){
            $query->andWhere(['LIKE', 'client_id', $this->client_id ]);
            $this->_filterContent .= Yii::t('app', 'ИД клиента') . ' = ' . $this->client_id . '; ' ;
        }

       //   $r = $query->createCommand()->getSql();

        return $query;
    }
}