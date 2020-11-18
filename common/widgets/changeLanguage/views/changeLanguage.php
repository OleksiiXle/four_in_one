<?php
$this->registerJs("
    var _languagesList = {$languagesList};
    var _selectedLanguage = '{$selectedLanguage}';
    var _changeLanguageRoute = '{$changeLanguageRoute}';
",\yii\web\View::POS_HEAD);

?>
<div id='selectedLanguage' class='selectedLanguage'>
    <b><?=$languagesListArray[$selectedLanguage]?></b>
</div>
