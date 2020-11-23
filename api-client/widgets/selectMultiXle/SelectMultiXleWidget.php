<?php
namespace app\widgets\selectMultiXle;

use yii\base\Widget;

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
    public $itemsArray;
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
        SelectMultiXleAssets::register($view);
        $view->registerJs("jQuery('#$this->selectId')
            .selectMultiXle('$this->selectId', '$textAreaAttributeId');");

        return $this->render('selectMultiXle',
            [
                'selectId' => $this->selectId,
                'itemsArray' => $this->itemsArray,
                'textAreaAttributeId' => $textAreaAttributeId,
                'textAreaAttributeName' => $textAreaAttributeName,
                'label' => $this->label,
            ]);
    }
}
