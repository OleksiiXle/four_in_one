<?php
namespace app\widgets\changeLanguage;

use Yii;
use yii\base\Widget;
use app\components\models\Translation;

class ChangeLanguageWidget extends Widget
{
    public $languagesList;
    public $selectedLanguage;
    public $changeLanguageRoute = '/adminxx/translation/change-language';

    public function init()
    {
        // /var/www/xle/test/widgets/changeLanguage/view/
        // /var/www/xle/test/widgets/changeLanguage/views/changeLanguage.php
        $this->languagesList = Translation::LIST_LANGUAGES;
        $this->selectedLanguage = Yii::$app->language;
        parent::init();
    }

    public function run()
    {
        $view = $this->getView();
        ChangeLanguageAssets::register($view);
        return $this->render('changeLanguage',[
            'languagesList' => json_encode($this->languagesList),
            'languagesListArray' => $this->languagesList,
            'selectedLanguage' => $this->selectedLanguage,
            'changeLanguageRoute' => $this->changeLanguageRoute,
            ]);
    }

}
