<?php

namespace app\modules\adminxx\components\rules;
use common\helpers\AccessHelper;
use yii\rbac\Rule;

class ProposalCRUDRule extends Rule
{
    /**
     * @inheritdoc
     */
    public $name = 'proposalCRUDRule';

    /**
     * @param $user integer - id текущего пользователя
     * @param $item array - информация об разрешении или роли, из которых вызвано правило:
    yii\rbac\Permission::__set_state(array(
    'type' => '2',
    'name' => '_orgStatProposalCRUD',
    'description' => '',
    'ruleName' => 'proposalCRUDRule',
    'data' => NULL,
    'createdAt' => '1552984135',
    'updatedAt' => '1552984135',
    ))
     * @param $params array - массив параметров,
     * задается в \Yii::$app->user->can('_orgStatProposalCRUD',  [
             'rootDepartments' => $rootDepartments,
             'currentDepartment' => $staffOrderMain->department_id,
    ]);
     *
     *
     */
    public function execute($user, $item, $params)
    {
        if (!empty($params)){
            return (isset($params['rootDepartments'][$params['currentDepartment']]));
        } else {
            return true;
        }
    }
}
