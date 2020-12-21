<?php
namespace apiadmin\controllers;

use yii\filters\VerbFilter;
use common\components\AccessControl;

/**
 * Site controller
 */
class SiteController extends \common\controllers\SiteController
{
    public $layout = '@apiadmin/modules/adminxx/views/layouts/adminxxLayout.php';
}
