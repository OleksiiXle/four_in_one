<?php
namespace app\modules\adminxx\models\filters;

use Yii;
use app\components\models\Translation;
use app\widgets\xlegrid\models\GridFilter;

class TranslationFilter extends GridFilter
{
    public $queryModel = Translation::class;

    public $id;
    public $messageRU = '';

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
    public $messageUK = '';
    public $messageEN = '';

    private $_dataForAutocomplete = null;

    /*
             $dataForAutocompleteRu = Translation::getDataForAutocomplete('ru-RU', 'app');
        $dataForAutocompleteEn = Translation::getDataForAutocomplete('en-US', 'app');
        $dataForAutocompleteUk = Translation::getDataForAutocomplete('uk-UK', 'app');

     */

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

    public function getQuery()
    {
        $tmp = 1;
        $attributesEmpty = true;
        $query = Translation::find();
        $this->_filterContent = [];
        if ($this->showOnlyChecked =='1' && !empty($this->checkedIdsJSON)) {
            $checkedIds = json_decode($this->checkedIdsJSON);
            $query->andWhere(['IN', 'id', $checkedIds]);
            $this->_filterContent[] = Yii::t('app', 'Только отмеченные') ;
            return $query;
        }

        if (!$this->validate()) {
            return $query;
        }

        if (!empty($this->messageRU)) {
            $query->andWhere(['LIKE', 'message', $this->messageRU])
                  ->andWhere(['language' => 'ru-RU']);
            $this->_filterContent[] = Yii::t('app', 'Русский')
                . ' (' . $this->messageRU . ')'
            ;
            $attributesEmpty = false;
        }

        if (!empty($this->messageEN)) {
            $query->andWhere(['LIKE', 'message', $this->messageEN])
                  ->andWhere(['language' => 'en-US']);
            $this->_filterContent[] = Yii::t('app', 'Английский')
                . ' (' . $this->messageEN . ')'
            ;
            $attributesEmpty = false;
        }

        if (!empty($this->messageUK)) {
            $query->andWhere(['LIKE', 'message', $this->messageUK])
                  ->andWhere(['language' => 'uk-UK']);
            $this->_filterContent[] = Yii::t('app', 'Украинский')
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

    public function getDataForUpload()
    {
        return [
            'id' => [
                'label' => 'id',
                'content' => 'value'
            ],
            'app' => [
                'label' => 'app',
                'content' => 'value'
            ],
            'language' => [
                'label' => 'language',
                'content' => 'value'
            ],
            'message' => [
                'label' => 'message',
                'content' => 'value'
            ],
            /*
            'status' => [
                'label' => 'Статус',
                'content' => function($model)
                {
                    return ($model->status == UserM::STATUS_ACTIVE) ? 'active' : 'not active';
                }
            ],
            */
        ];
    }

}