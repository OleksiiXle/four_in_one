<?php

namespace apiadmin\modules\adminxx\grids\filters;

use Yii;
use common\components\models\Translation;
use \common\widgets\xgrid\models\GridFilter;

class TranslationFilter extends \common\widgets\xgrid\models\GridFilter
{
    public $queryModel = Translation::class;

    public $id;
    public $messageUK = '';
    public $messageEN = '';
    public $messageRU = '';

    private $_dataForAutocomplete = null;

    /**
     * @return mixed
     */
    public function getDataForAutocomplete()
    {
        if ($this->_dataForAutocomplete === null) {
            foreach (Translation::LIST_LANGUAGES as $key => $value) {
                $this->_dataForAutocomplete[$key] = Translation::getDataForAutocomplete($key, 'app');
            }
        }

        return $this->_dataForAutocomplete;
    }

    public function rules()
    {
        $rules = [
            [['messageRU', 'messageUK', 'messageEN'], 'string', 'max' => 255],
            [['messageRU', 'messageUK', 'messageEN'], 'trim'],
            [['messageRU', 'messageUK', 'messageEN'], 'match', 'pattern' => Translation::NAME_PATTERN,
                'message' => Yii::t('app', Translation::NAME_ERROR_MESSAGE)],
        ];

        return array_merge($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'messageRU' => Yii::t('app', 'Русский'),
            'messageUK' => Yii::t('app', 'Ураїнський'),
            'messageEN' => Yii::t('app', 'English'),
        ];
    }

    public function getCustomQuery()
    {
        $query = Translation::find();

        return $query;
    }

    public function getQuery()
    {
        $query = $this->defaultQuery;
        if (!$this->validate()) {
            return $query;
        }
        $attributesEmpty = true;
        if (!empty($this->messageRU)) {
            $query->andWhere(['LIKE', 'message', $this->messageRU])
                ->andWhere(['language' => 'ru-RU']);
            $this->_filterContent .= Yii::t('app', 'Русский')
                . ' (' . $this->messageRU . ')'
            ;
            $attributesEmpty = false;
        }

        if (!empty($this->messageEN)) {
            $query->andWhere(['LIKE', 'message', $this->messageEN])
                ->andWhere(['language' => 'en-US']);
            $this->_filterContent .= Yii::t('app', 'Английский')
                . ' (' . $this->messageEN . ')'
            ;
            $attributesEmpty = false;
        }

        if (!empty($this->messageUK)) {
            $query->andWhere(['LIKE', 'message', $this->messageUK])
                ->andWhere(['language' => 'uk-UK']);
            $this->_filterContent .= Yii::t('app', 'Украинский')
                . ' (' . $this->messageUK . ')'
            ;
            $attributesEmpty = false;
        }

        if ($attributesEmpty) {
            $query->andWhere(['language' => \Yii::$app->language]);
        }
        //   $e = $query->createCommand()->getSql();

        return $query;
    }
}