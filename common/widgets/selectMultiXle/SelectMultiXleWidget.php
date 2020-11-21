<?php
namespace common\widgets\selectMultiXle;

use yii\base\Widget;
use yii\helpers\Json;


/**
 * Class SelectMultiXleWidget
 * Множественный выбор, результат выбора записывается в формате JSON в указанный атрибут
 * @package app\widgets\selectMultiXle
 */
class SelectMultiXleWidget extends Widget
{
    /**
     * Если не задано, формируется автоматически
     * @var
     */
    public $selectId;
    /**
     * Ассоциативный массив значений для выбора [key =-> value]
     * @var
     */
    public $itemsArrayToSelect;
    /**
     * Уже имеющиеся значения [key =-> value]
     * @var
     */
    public $itemsArrayOwn = [];
    /**
     * Имя класса модели без неймспейса
     * @var
     */
    public $modelName;
    /**
     * @var
     * Имя аттрибута модели, куда будет писаться результат выбора
     */
    public $textAreaAttribute;
    /**
     * Подпись для аттрибута
     * @var
     */
    public $label;

    /**
     *
     */
    public function init()
    {
        parent::init();
        if (empty($this->selectId)) {
            $this->selectId = 'xleMultiSelect_' . $this->getId();
        }
    }

    /**
     * @return string
     */
    public function run()
    {
        $textAreaAttributeId = strtolower($this->modelName . '-' . $this->textAreaAttribute);
        $textAreaAttributeName = $this->modelName . '[' . $this->textAreaAttribute . ']';
        $view = $this->getView();
        $itemsArrayOwn = (!empty($this->itemsArrayOwn)) ? Json::htmlEncode($this->itemsArrayOwn) : '{}';
        SelectMultiXleAssets::register($view);
        $view->registerJs("jQuery('#$this->selectId')
            .selectMultiXle('$this->selectId', '$textAreaAttributeId', '$itemsArrayOwn');");

        return $this->render('selectMultiXle',
            [
                'selectId' => $this->selectId,
                'itemsArrayToSelect' => $this->itemsArrayToSelect,
                'itemsArrayOwn' => $this->itemsArrayOwn,
                'textAreaAttributeId' => $textAreaAttributeId,
                'textAreaAttributeName' => $textAreaAttributeName,
                'label' => $this->label,
            ]);
    }
}
