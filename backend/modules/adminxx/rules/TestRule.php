<?php

namespace backend\modules\adminxx\rules;

use yii\rbac\Rule;

class TestRule extends Rule
{
    /**
     * @inheritdoc
     */
    public $name = 'testRule';

    /**
     * @param $user integer - id текущего пользователя
     * @param $item array - информация об разрешении или роли, из которых вызвано правило:
     * @param $params array - массив параметров,
     */
    public function execute($user, $item, $params)
    {
        $i=1;
        return true;
    }
}
