<?php

namespace apiadmin\modules\adminxx\grids\filters;

use apiadmin\modules\adminxx\models\oauth\OauthCient;
use Yii;
use common\widgets\xgrid\models\GridFilter;

class OauthClientFilter extends GridFilter
{
    const IP_PATTERN       = '/^[0-9 .]+$/ui'; //--маска для пароля
    const IP_ERROR_MESSAGE = 'Допустиные символы - цифры и точка'; //--сообщение об ошибке
    public $queryModel = OauthCient::class;

    public $client_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $ownRules = [
            [['client_id', ], 'string', 'max' => 32],
        ];

        return array_merge(parent::rules(), $ownRules);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_id' => 'ID',
        ];
    }

    public function getCustomQuery()
    {
        $query = OauthCient::find();

        return $query;
    }


    public function getQuery()
    {
        $query = $this->defaultQuery;

        //---------------------------------------------------------------------------------- USER

        if (!empty($this->client_id)){
            $query->andWhere(['LIKE', 'client_id', $this->client_id ]);
            $this->_filterContent .= Yii::t('app', 'client_id') . ' = ' . $this->client_id . '; ' ;
        }

       //   $r = $query->createCommand()->getSql();

        return $query;
    }
}