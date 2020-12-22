<?php

namespace app\modules\adminxx\models;

use common\models\UserM;
use Yii;
use common\models\MainModel;

/**
 * This is the model class for table "auth_item".
 *
 * @property string $name
 * @property int $type
 * @property string $description
 * @property string $rule_name
 * @property resource $data
 * @property int $created_at
 * @property int $updated_at
 *
 * @property AuthItemX[] $children
 * @property AuthItemX[] $parents
 */
class AuthItemX extends \yii\db\ActiveRecord
{
    const TYPE_ROLE = 1;
    const TYPE_PERMISSION = 2;
    const TYPE_ROUTE = 3;
    const TYPE_All = 0;


    public static $typeDict = [
        self::TYPE_All => 'Все типы',
        self::TYPE_ROLE => 'Роли',
       self::TYPE_PERMISSION => 'Разрешения',
      // self::TYPE_ROUTE => 'Маршрути',

    ];

    public static function getTypeDict()
    {
        return [
            self::TYPE_All => \Yii::t('app', 'Все типы'),
            self::TYPE_ROLE => \Yii::t('app', 'Роли'),
            self::TYPE_PERMISSION => \Yii::t('app', 'Разрешения'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth_item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['name'], 'unique'],
            [['name'], 'match', 'pattern' => UserM::USER_PASSWORD_PATTERN,
                'message' => \Yii::t('app', UserM::USER_PASSWORD_ERROR_MESSAGE)],
            [['description'], 'match', 'pattern' => MainModel::PATTERN_TEXT,
                'message' => \Yii::t('app', MainModel::PATTERN_TEXT_ERROR_MESSAGE)],
            [['name', 'rule_name'], 'string', 'min' => 4, 'max' => 64],
            [['description'], 'string', 'max' => 255],
            [['type', 'created_at', 'updated_at'], 'integer'],
        //  [['rule_name'], 'exist', 'skipOnError' => true, 'targetClass' => Aut::className(), 'targetAttribute' => ['rule_name' => 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => \Yii::t('app', 'Название'),
            'type' => \Yii::t('app', 'Тип'),
            'description' => \Yii::t('app', 'Описание'),
            'rule_name' => \Yii::t('app', 'Правило'),
            'data' => 'Data',
            'created_at' => \Yii::t('app', 'Создано'),
            'updated_at' => \Yii::t('app', 'Изменено'),
        ];
    }


    //************* ПЕРЕОПЕРДЕЛЕННЫЕ МЕТОДЫ

    public function save($runValidation = true, $attributeNames = null) {
        if ($this->validate()) {
            $manager = Yii::$app->authManager;
            if ($this->type == self::TYPE_ROLE) {
                $item = $manager->createRole($this->name);
            } else {
                $item = $manager->createPermission($this->name);
            }
            $item->name = $this->name;
            $item->description = $this->description;
            $item->ruleName = ($this->rule_name == \Yii::t('app', 'Без правила')) ? null : $this->rule_name;
            //  $item->data = $this->data === null || $this->data === '' ? null : Json::decode($this->data);

            if ($this->isNewRecord){
                $manager->add($item);
            } else {
                $oldName = $this->getOldAttribute('name');
                $manager->update($oldName, $item);
            }
            return true;
        } else {
            return false;
        }
    }

    public static function getRulesList(){
        $rules = \Yii::$app->authManager->getRules();
        $ret[\Yii::t('app', 'Без правила')] = \Yii::t('app', 'Без правила');
        foreach ($rules as $rule){
            $ret[$rule->name] =$rule->name;
        }
       return $ret;

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(AuthItemX::class, ['name' => 'child'])->viaTable('auth_item_child', ['parent' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParents()
    {
        return $this->hasMany(AuthItemX::class, ['name' => 'parent'])->viaTable('auth_item_child', ['child' => 'name']);
    }

    /**
     * +++ Получение ролей, разрешений и маршрутов итема (роль или разрешение)
     * @return array
     */
    public static function getItemsXle($type, $name)
    {
        $manager = Yii::$app->getAuthManager();
        $result['assigned']['Roles']=[];
        $result['avaliable']['Roles']=[];
        switch ($type){
            case self::TYPE_ROLE:
                //--  роли
                $assigned     = array_keys($manager->getChildRoles($name));
                $avaliableAll = array_keys($manager->getRoles());
                $avaliable    = array_diff($avaliableAll , $assigned);
                $result['assigned']['Roles']=$assigned;
                $result['avaliable']['Roles']=$avaliable;
            case self::TYPE_PERMISSION:
                //-- разрешения
                $buffAssigned     = array_keys($manager->getPermissionsByRole($name));
                $buffAvaliableAll = array_keys($manager->getPermissions());
                $assignedPermissions = $assignedRoutes =[];
                $avaliablePermissions = $avaliableRoutes =[];
                foreach ($buffAssigned as $name) {
                    if ($name[0] != '/') {
                        $assignedPermissions[] = $name;
                    } else {
                        $assignedRoutes[] = $name;
                    }
                }

                foreach ($buffAvaliableAll as $name) {
                    if ($name[0] != '/') {
                        $avaliablePermissions[] = $name;
                    } else {
                        $avaliableRoutes[] = $name;
                    }
                }
                $result['assigned']['Permissions']=$assignedPermissions;
                $result['avaliable']['Permissions']=array_diff($avaliablePermissions , $assignedPermissions);
                $result['assigned']['Routes']=$assignedRoutes;
                $result['avaliable']['Routes']=array_diff($avaliableRoutes , $assignedRoutes);


                break;
        }

        return $result;
    }




}
