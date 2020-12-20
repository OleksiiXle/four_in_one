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
    /*
     Yii::t('app', 'Удалить') => [
              'icon' => 'glyphicon glyphicon-trash',
              'route' => Url::toRoute(['/adminxx/user/delete-by-admin', 'user_id' => $data['id']]),
              'confirm' => Yii::t('app', 'Подтвердите удаление пользователя'),
          ],

     */
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
