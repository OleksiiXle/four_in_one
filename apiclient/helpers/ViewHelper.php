<?php

namespace app\helpers;

use app\components\models\Translation;
use Yii;
use yii\web\View;

class ViewHelper
{
    /*
     $tmp1 = [
    'Відео',
    'Власник',
    'Переводы',
    'Переклади'
];
\app\helpers\ViewHelper::setTranslationsForJS($this, $tmp1);

     */
    public static function setTranslationsForJS(View $view, $messages, $key = '')
    {
        if (empty($key)){
            $key = $view->context->id . '-' . $view->context->action->id;
        }
        $translations = Translation::getJsDictionary($key, $messages );
        $translationsJSON = json_encode($translations);
        $view->registerJs("addTranslations({$translationsJSON});",\yii\web\View::POS_HEAD);
    }
}