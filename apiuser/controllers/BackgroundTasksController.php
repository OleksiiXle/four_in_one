<?php
namespace apiuser\controllers;

use common\controllers\BackgroundTasksController as Controller;
use common\components\AccessControl;

/**
 * Class BackgroundTasksController
 * @package apiadmin\controllers
 */
class BackgroundTasksController extends Controller
{
    /**
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'start-task', 'check-task', 'test-background-task', 'upload-result', 'kill-task',
                        'get-background-tasks-pool'
                    ],
                    'roles'      => [
                        '@',
                    ],
                ],
            ],
        ];

        return $behaviors;
    }

}