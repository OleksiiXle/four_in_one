<?php
namespace app\modules\adminxx\models\filters;

use app\components\models\Provider;
use Yii;
use app\components\models\Translation;
use common\widgets\xlegrid\models\GridFilter;

class ProviderFilter extends GridFilter
{
    public $queryModel = Provider::class;

    public function getFilterContent()
    {
        if ($this->_filterContent === null) {
            $this->getQuery();
        }

        return $this->_filterContent;
    }

    public function rules()
    {
        $rules = [
        ];

        return array_merge($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
        ];
    }

    public function getQuery()
    {
        $query = Provider::find();

        return $query;


    }

    public function getDataForUpload()
    {
        return [
            'id' => [
                'label' => 'id',
                'content' => 'value'
            ],
        ];
    }

}