<?php

namespace common\components\rules;
use common\helpers\AccessHelper;
use yii\rbac\Rule;

class StaffOrderMainItemsAvailabledRule extends Rule
{
    /**
     * @inheritdoc
     */
    public $name = 'staffOrderMainItemsAvailabledRule';

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
    [
    'staff_order_main_id' => 0,
    'staff_order_id' => 0,
    'department_id' => 0,
    'position_id' => 0,
    ]
     *
     *
     */
    public function execute($user, $item, $params)
    {
        $i=1;
        if (!empty($params) && is_array($params)
            && isset($params['staff_order_main_id'])
            && isset($params['staff_order_id'])
            && isset($params['department_id'])
            && isset($params['position_id'])
        ){
            $staffOrderDepartment_id = 0;
            $staff_order_main_id = $params['staff_order_main_id'];
            $staff_order_id = $params['staff_order_id'];
            $department_id = $params['department_id'];
            $position_id = $params['position_id'];
            if (!empty($staff_order_main_id)){
                $query = (new \yii\db\Query)
                    ->select("som.department_id AS dep_id")
                    ->from("staff_order_main som")
                    ->where(['som.id' => $staff_order_main_id]);
            } elseif (!empty($staff_order_id)){
                $query = (new \yii\db\Query)
                    ->select("som.department_id AS dep_id")
                    ->from("staff_order_main som")
                    ->innerJoin("staff_order so", "som.id = so.staff_order_main_id")
                    ->where(['so.id' => $staff_order_id]);
              //  $rr = $query->createCommand()->getSql();
            } elseif (!empty($department_id)){
                $query = (new \yii\db\Query)
                    ->select("som.department_id AS dep_id")
                    ->from("staff_order_main som")
                    ->innerJoin("staff_order so", "som.id = so.staff_order_main_id")
                    ->innerJoin("order_project_department opd", "so.id = opd.staff_order_id")
                    ->where(['opd.id' => $department_id]);
            } elseif (!empty($position_id)){
                $query = (new \yii\db\Query)
                    ->select("som.department_id AS dep_id")
                    ->from("staff_order_main som")
                    ->innerJoin("staff_order so", "som.id = so.staff_order_main_id")
                    ->innerJoin("order_project_position opp", "so.id = opp.staff_order_id")
                    ->where(['opp.id' => $position_id]);
            }
            if (isset($query)){
                $staffOrderMain = $query->one();
            }
            $staffOrderDepartment_id = (!empty($staffOrderMain)) ? $staffOrderMain['dep_id'] : 0;
            //-------------
            if (!empty($staffOrderDepartment_id)){
                //-- если получилось определить $staff_order_main_id, проверяет его доступность
                $rootDepartments = AccessHelper::getRootDepartments();
                return (isset($rootDepartments[$staffOrderDepartment_id]));
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}
