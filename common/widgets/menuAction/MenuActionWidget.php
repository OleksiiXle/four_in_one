<?php

namespace common\widgets\menuAction;

use yii\base\Widget;

class MenuActionWidget extends Widget
{
    public $assetsRegister = true;
    public $icon = "glyphicon glyphicon-list";
    public $items = [
        'text' => 'route',
    ];
    public $offset = 0;
    public $confirm = '';

    public function run()
    {
        if ($this->assetsRegister) {
            $view = $this->getView();
            MenuActionAsset::register($view);
        }
        return $this->render('menuAction',
            [
                'icon' => $this->icon,
                'items' => $this->items,
                'offset' => $this->offset,
                'confirm' => $this->confirm,
            ]);
    }
}
