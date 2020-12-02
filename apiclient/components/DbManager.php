<?php

namespace app\components;

use Yii;
use yii\db\Query;
use yii\rbac\Assignment;
use yii\rbac\BaseManager;
use yii\rbac\Item;
use yii\rbac\Permission;
use yii\rbac\Role;
use yii\rbac\Rule;
use yii\web\ForbiddenHttpException;

use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\caching\CacheInterface;
use yii\db\Connection;
use yii\db\Expression;
use yii\di\Instance;
use app\modules\adminxx\models\User; //todo отвязаться от него

class DbManager extends BaseManager
{
    /**
     * @var array
     */
    private $_checkAccessAssignments = [];
    /**
     * Роли (все, с потомками) текущего пользователя - массив [имя => имя правила/""]
     * @var
     */
    private $_userRoles;
    /**
     * Роли и разрешения (все, с потомками) текущего пользователя - массив [имя => имя правила/""]
     * @var
     */
    private $_userRolesPermissions;
    /**
     * ИД текущего пользователя
     * @var
     */
    private $_userId;

    /**
     * Источник кеширования ( 'cache' - кеш сайта, 'session' - сессия пользователя, "" - без кеширования, все берется из БД)
     * @var string
     */
    public $cacheSource = 'session';//'cache';
    /**
     * @var string
     */
    public $permCacheKey = 'perm';
    /**
     * @var int
     */
    public $permCacheKeyDuration = 180;

    //******************************************************************
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * After the DbManager object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db = 'db';
    /**
     * @var string the name of the table storing authorization items. Defaults to "auth_item".
     */
    public $itemTable = '{{%auth_item}}';
    /**
     * @var string the name of the table storing authorization item hierarchy. Defaults to "auth_item_child".
     */
    public $itemChildTable = '{{%auth_item_child}}';
    /**
     * @var string the name of the table storing authorization item assignments. Defaults to "auth_assignment".
     */
    public $assignmentTable = '{{%auth_assignment}}';
    /**
     * @var string the name of the table storing rules. Defaults to "auth_rule".
     */
    public $ruleTable = '{{%auth_rule}}';
    /**
     * @var CacheInterface|array|string the cache used to improve RBAC performance. This can be one of the following:
     *
     * - an application component ID (e.g. `cache`)
     * - a configuration array
     * - a [[\yii\caching\Cache]] object
     *
     * When this is not set, it means caching is not enabled.
     *
     * Note that by enabling RBAC cache, all auth items, rules and auth item parent-child relationships will
     * be cached and loaded into memory. This will improve the performance of RBAC permission check. However,
     * it does require extra memory and as a result may not be appropriate if your RBAC system contains too many
     * auth items. You should seek other RBAC implementations (e.g. RBAC based on Redis storage) in this case.
     *
     * Also note that if you modify RBAC items, rules or parent-child relationships from outside of this component,
     * you have to manually call [[invalidateCache()]] to ensure data consistency.
     *
     * @since 2.0.3
     */
    public $cache;
    /**
     * @var string the key used to store RBAC data in cache
     * @see cache
     * @since 2.0.3
     */
    public $cacheKey = 'rbac';

    /**
     * @var Item[] all auth items (name => Item)
     */
    protected $items;
    /**
     * @var Rule[] all auth rules (name => Rule)
     */
    protected $rules;
    /**
     * @var array auth item parent-child relationships (childName => list of parents)
     */
    protected $parents;

    //******************************************************************


    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $user = \Yii::$app->user;
        $this->_userId = $user->id;
        $this->cacheSource = \Yii::$app->configs->rbacCacheSource;

        if (isset($this->_userId)){
            if ($this->cacheSource == 'session' && $user->getIdentity()->needRefreshPermissions()){
                $this->invalidatePermCache($user->id);
                $r = $user->getIdentity()->dropRefreshPermissions();
            }
        }
        $this->permCacheKey = \Yii::$app->configs->permCacheKey;
        $this->permCacheKeyDuration = \Yii::$app->configs->permCacheKeyDuration;
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
        if ($this->cache !== null) {
            $this->cache = Instance::ensure($this->cache, 'yii\caching\CacheInterface');
        }
    }

    /**
     * @return mixed
     */
    public function getUserRoles()
    {
        $w = 1;

        if (!isset($this->_userRoles)){
            if (!empty($this->_userId)){
                $this->getUserRolesPermissionsFrom();
            } else {
                $this->_userRoles = [];
            }
        }
        return $this->_userRoles;
    }

    public function getUserRolesFromDB()
    {
        $this->_userRoles = [];
        //-- все роли, назначенные юсеру
        $userSelfRoles =$this->getRolesByUser($this->_userId);
        //-- все роли, назначенные юсеру, и их потомки
        foreach ($userSelfRoles as $userSelfRole){
            $this->_userRoles[$userSelfRole->name] = (isset($userSelfRole->ruleName)) ? $userSelfRole->ruleName : '';
            $this->getRoleChildrenRecursive($userSelfRole->name, $this->_userRoles);
        }
    }

    public function getUserRolesPermissionsFromDB()
    {
        $this->_userRolesPermissions = [];
        if (!isset($this->_userRoles)){
            $this->getUserRolesFromDB();
        }
        //-- разрешения ролей юсера и их потомков
        $this->_userRolesPermissions = $this->_userRoles;
        foreach ($this->_userRoles as $userRole => $rule){
            $perm = $this->getPermissionsByRole($userRole);
            foreach ($perm as $item){
                if (!isset($this->_userRolesPermissions[$item->name])){
                    $this->_userRolesPermissions[$item->name] = isset($item->ruleName) ? $item->ruleName : '';
                }
            }
        }
        //-- все разрешения юсера и их потомки
        $userSelfPermissions =$this->getPermissionsByUser($this->_userId);

        foreach ($userSelfPermissions as $userSelfPermission){
            if (!isset($this->_userRolesPermissions[$userSelfPermission->name])){
                $this->_userRolesPermissions[$userSelfPermission->name] = ($userSelfPermission->ruleName)
                    ? $userSelfPermission->ruleName
                    : '';
            }
           // $this->getPermissionChildrenRecursive($userSelfPermission->name, $this->_userRolesPermissions);
        }

    }


    /**
     * @return mixed
     */
    public function getUserRolesPermissions()
    {
        $w = 1;
        if (!isset($this->_userRolesPermissions)){
            if (!empty($this->_userId)){
                $this->getUserRolesPermissionsFrom();
            } else {
                $this->_userRolesPermissions = [];
            }
        }
        return $this->_userRolesPermissions;
    }

    /**
     * @param $roleName
     * @param $target
     */
    public function getRoleChildrenRecursive($roleName, &$target)
    {
        $query = new Query();
        $children = $query
            ->select("ch.child, par.rule_name")
            ->from("$this->itemChildTable ch" )
            ->innerJoin("$this->itemTable par", "ch.child = par.name")
            ->where(['ch.parent' => $roleName, 'par.type' => 1])
            ->all($this->db);
        foreach ($children as $child) {
            $target[$child['child']] = (!empty($child['rule_name'])) ? $child['rule_name'] : '';
            $this->getRoleChildrenRecursive($child, $target);
        }

    }

    /**
     * @param $roleName
     * @param $target
     */
    public function getPermissionChildrenRecursive($roleName, &$target)
    {
        $query = new Query();
        $children = $query
            ->select("ch.child, par.rule_name")
            ->from("$this->itemChildTable ch" )
            ->innerJoin("$this->itemTable par", "ch.child = par.name")
            ->where(['ch.parent' => $roleName, 'par.type' => 2])
            ->all($this->db);
        foreach ($children as $child) {
            $target[$child['child']] = (!empty($child['rule_name'])) ? $child['rule_name'] : '';
            $this->getPermissionChildrenRecursive($child, $target);
        }

    }

    /**
     * Получение ролей и разрешений из хранилища
     * Если кеш пустой - их определение и запись в кеш
     * @return boolean
     */
    public function getUserRolesPermissionsFrom()
    {
        $rr = 1;
        switch ($this->cacheSource){
            case 'cache':
                $ret = $this->getUserRolesPermissionsFromCache();
                break;
            case 'session':

                $ret = $this->getUserRolesPermissionsFromSession();
                break;
            default:
                $this->getUserRoles();
                $this->getUserRolesPermissions();
                break;
        }
        return $ret;
    }

    /**
     * Получение ролей и разрешений из кеша
     * Если кеш пустой - их определение и запись в кеш
     * @return boolean
     */
    public function getUserRolesPermissionsFromCache()
    {
        // $this->invalidateCache();
        $this->_userRoles = [];
        $this->_userRolesPermissions = [];
        if (empty($this->_userId)){
            return false;
        }

        $data = $this->cache->get($this->permCacheKey);
        if (is_array($data) && isset($data[$this->_userId])) {
            //-- если разрешения есть в кеше - берем их оттуда
            $this->_userRoles = (!empty($data[$this->_userId]['userRoles'])) ? $data[$this->_userId]['userRoles'] : [];
            $this->_userRolesPermissions = (!empty($data[$this->_userId]['userRolesPermissions'])) ? $data[$this->_userId]['userRolesPermissions'] : [];
            $ret = true;
        } else {
            //-- если разрешений в кеше нет, определяем, запись в кеш, возвращаем
            $this->getUserRolesFromDB();
            $this->getUserRolesPermissionsFromDB();
            //-- запись в кеш
            if (!is_array($data)){
                $data = [];
            }
            $data [$this->_userId] = [
                'userRoles' => $this->_userRoles,
                'userRolesPermissions' => $this->_userRolesPermissions,
            ];

            $ret = $this->cache->set($this->permCacheKey, $data, $this->permCacheKeyDuration);
            if (!$ret){
                throw new ForbiddenHttpException('Cache save error');
            }
        }
        return $ret;
    }

    /**
     * Получение ролей и разрешений из сессии
     * Если сессия пустая - их определение и запись
     * @return boolean
     */
    public function getUserRolesPermissionsFromSession()
    {
        //$this->invalidateCache();
        $this->_userRoles = [];
        $this->_userRolesPermissions = [];

        $session = \Yii::$app->session;
        //  $session->remove('userRoles');
        //  $session->remove('userRolesPermissions');

        if($session->has('userRoles') || $session->has('userRolesPermissions') ){
            //-- если разрешения есть в сессии - берем их оттуда
            $this->_userRoles = $session->get('userRoles');
            $this->_userRolesPermissions = $session->get('userRolesPermissions');
            $ret = true;
        } else {
            //-- если разрешений в кеше нет, определяем, запись в сессию, возвращаем
            $this->getUserRolesFromDB();
            $this->getUserRolesPermissionsFromDB();

            //-- запись в сессию

            $session->set('userRoles', $this->_userRoles);
            $session->set('userRolesPermissions', $this->_userRolesPermissions);
            $ret = true;
        }
        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAccess($userId, $permissionName, $params = [])
    {
        $t=1;
        $userRolesPermissions = $this->getUserRolesPermissions() ;
        $rr = 1;
        if (!empty($userRolesPermissions) && isset($userRolesPermissions[$permissionName])) {
            //-- если у юсера есть роли или разрешения вообще и среди них есть $permissionName
            if (!empty($userRolesPermissions[$permissionName])){
                //-- если у роли или разрешения есть правило
                $item = $this->getItem($permissionName);
                return $this->executeRule($userId, $item, $params);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Сброс кеша разрешений пользователей,
     * если $userId не 0 - сбрасываются данные только одного пользователя
     * @param int $userId
     * @return bool
     */
    public function invalidatePermCache($userId=0)
    {
        $ret = true;
        switch ($this->cacheSource){
            case 'cache':
                if ($this->cache !== null) {
                    if ($userId > 0){
                        $data = $this->cache->get($this->permCacheKey);
                        if (is_array($data) && isset($data[$userId])) {
                            unset($data[$userId]);
                        }
                        if (!empty($data)){
                            $ret = $this->cache->set($this->permCacheKey, $data, $this->permCacheKeyDuration);
                        } else {
                            $ret = $this->cache->delete($this->permCacheKey);
                        }
                    } elseif ($this->cache->exists($this->permCacheKey)) {
                        $ret = $this->cache->delete($this->permCacheKey);
                    }
                }
                break;
            case 'session':
                $session = \Yii::$app->session;
                $session->remove('userRoles');
                $session->remove('userRolesPermissions');
                break;
            default:
                break;
        }
        $this->_userRoles  = null;
        $this->_userRolesPermissions  = null;
        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function assign($role, $userId)
    {
        $ret = $this->invalidatePermCache($userId); //--xle
        $assignment = new Assignment([
            'userId' => $userId,
            'roleName' => $role->name,
            'createdAt' => time(),
        ]);

        $this->db->createCommand()
            ->insert($this->assignmentTable, [
                'user_id' => $assignment->userId,
                'item_name' => $assignment->roleName,
                'created_at' => $assignment->createdAt,
            ])->execute();

        unset($this->_checkAccessAssignments[(string) $userId]);
        return $assignment;
    }

    /**
     * {@inheritdoc}
     */
    public function revoke($role, $userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return false;
        }
        $ret = $this->invalidatePermCache($userId); //--xle
        unset($this->_checkAccessAssignments[(string) $userId]);
        return $this->db->createCommand()
                ->delete($this->assignmentTable, ['user_id' => (string) $userId, 'item_name' => $role->name])
                ->execute() > 0;
    }

    /**
     *
     */
    public function invalidateCache()
    {
        switch ($this->cacheSource){
            case 'cache':
                if ($this->cache !== null) {
                    $this->cache->delete($this->cacheKey);
                    $this->items = null;
                    $this->rules = null;
                    $this->parents = null;
                    $ret = $this->invalidatePermCache(0); //--xle
                }
                break;
            case 'session':
                $rett = User::updateAll(['refresh_permissions' => true]);
                $ret = $this->invalidatePermCache(0); //--xle
                break;
        }
        $this->_checkAccessAssignments = [];
    }

    /**
     * {@inheritdoc}
     */
    public function removeAllAssignments()
    {
        $this->_checkAccessAssignments = [];
        $this->db->createCommand()->delete($this->assignmentTable)->execute();
        $ret = $this->invalidatePermCache(0); //--xle

    }

    /**
     * {@inheritdoc}
     */
    public function revokeAll($userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return false;
        }
        $ret = $this->invalidatePermCache(0); //--xle
        unset($this->_checkAccessAssignments[(string) $userId]);
        return $this->db->createCommand()
                ->delete($this->assignmentTable, ['user_id' => (string) $userId])
                ->execute() > 0;
    }

//*********************************************************************************************************************

    /**
     * Performs access check for the specified user based on the data loaded from cache.
     * This method is internally called by [[checkAccess()]] when [[cache]] is enabled.
     * @param string|int $user the user ID. This should can be either an integer or a string representing
     * the unique identifier of a user. See [[\yii\web\User::id]].
     * @param string $itemName the name of the operation that need access check
     * @param array $params name-value pairs that would be passed to rules associated
     * with the tasks and roles assigned to the user. A param with name 'user' is added to this array,
     * which holds the value of `$userId`.
     * @param Assignment[] $assignments the assignments to the specified user
     * @return bool whether the operations can be performed by the user.
     * @since 2.0.3
     */
    protected function checkAccessFromCache($user, $itemName, $params, $assignments)
    {
        if (!isset($this->items[$itemName])) {
            return false;
        }

        $item = $this->items[$itemName];

        Yii::debug($item instanceof Role ? "Checking role: $itemName" : "Checking permission: $itemName", __METHOD__);

        if (!$this->executeRule($user, $item, $params)) {
            return false;
        }

        if (isset($assignments[$itemName]) || in_array($itemName, $this->defaultRoles)) {
            return true;
        }

        if (!empty($this->parents[$itemName])) {
            foreach ($this->parents[$itemName] as $parent) {
                if ($this->checkAccessFromCache($user, $parent, $params, $assignments)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Performs access check for the specified user.
     * This method is internally called by [[checkAccess()]].
     * @param string|int $user the user ID. This should can be either an integer or a string representing
     * the unique identifier of a user. See [[\yii\web\User::id]].
     * @param string $itemName the name of the operation that need access check
     * @param array $params name-value pairs that would be passed to rules associated
     * with the tasks and roles assigned to the user. A param with name 'user' is added to this array,
     * which holds the value of `$userId`.
     * @param Assignment[] $assignments the assignments to the specified user
     * @return bool whether the operations can be performed by the user.
     */
    protected function checkAccessRecursive($user, $itemName, $params, $assignments)
    {
        if (($item = $this->getItem($itemName)) === null) {
            return false;
        }

        Yii::debug($item instanceof Role ? "Checking role: $itemName" : "Checking permission: $itemName", __METHOD__);

        if (!$this->executeRule($user, $item, $params)) {
            return false;
        }

        if (isset($assignments[$itemName]) || in_array($itemName, $this->defaultRoles)) {
            return true;
        }

        $query = new Query();
        $parents = $query->select(['parent'])
            ->from($this->itemChildTable)
            ->where(['child' => $itemName])
            ->column($this->db);
        foreach ($parents as $parent) {
            if ($this->checkAccessRecursive($user, $parent, $params, $assignments)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getItem($name)
    {
        if (empty($name)) {
            return null;
        }

        if (!empty($this->items[$name])) {
            return $this->items[$name];
        }

        $row = (new Query())->from($this->itemTable)
            ->where(['name' => $name])
            ->one($this->db);

        if ($row === false) {
            return null;
        }

        return $this->populateItem($row);
    }

    /**
     * Returns a value indicating whether the database supports cascading update and delete.
     * The default implementation will return false for SQLite database and true for all other databases.
     * @return bool whether the database supports cascading update and delete.
     */
    protected function supportsCascadeUpdate()
    {
        return strncmp($this->db->getDriverName(), 'sqlite', 6) !== 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function addItem($item)
    {
        $time = time();
        if ($item->createdAt === null) {
            $item->createdAt = $time;
        }
        if ($item->updatedAt === null) {
            $item->updatedAt = $time;
        }
        $this->db->createCommand()
            ->insert($this->itemTable, [
                'name' => $item->name,
                'type' => $item->type,
                'description' => $item->description,
                'rule_name' => $item->ruleName,
                'data' => $item->data === null ? null : serialize($item->data),
                'created_at' => $item->createdAt,
                'updated_at' => $item->updatedAt,
            ])->execute();

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function removeItem($item)
    {
        if (!$this->supportsCascadeUpdate()) {
            $this->db->createCommand()
                ->delete($this->itemChildTable, ['or', '[[parent]]=:name', '[[child]]=:name'], [':name' => $item->name])
                ->execute();
            $this->db->createCommand()
                ->delete($this->assignmentTable, ['item_name' => $item->name])
                ->execute();
        }

        $this->db->createCommand()
            ->delete($this->itemTable, ['name' => $item->name])
            ->execute();

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateItem($name, $item)
    {
        if ($item->name !== $name && !$this->supportsCascadeUpdate()) {
            $this->db->createCommand()
                ->update($this->itemChildTable, ['parent' => $item->name], ['parent' => $name])
                ->execute();
            $this->db->createCommand()
                ->update($this->itemChildTable, ['child' => $item->name], ['child' => $name])
                ->execute();
            $this->db->createCommand()
                ->update($this->assignmentTable, ['item_name' => $item->name], ['item_name' => $name])
                ->execute();
        }

        $item->updatedAt = time();

        $this->db->createCommand()
            ->update($this->itemTable, [
                'name' => $item->name,
                'description' => $item->description,
                'rule_name' => $item->ruleName,
                'data' => $item->data === null ? null : serialize($item->data),
                'updated_at' => $item->updatedAt,
            ], [
                'name' => $name,
            ])->execute();

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function addRule($rule)
    {
        $time = time();
        if ($rule->createdAt === null) {
            $rule->createdAt = $time;
        }
        if ($rule->updatedAt === null) {
            $rule->updatedAt = $time;
        }
        $this->db->createCommand()
            ->insert($this->ruleTable, [
                'name' => $rule->name,
                'data' => serialize($rule),
                'created_at' => $rule->createdAt,
                'updated_at' => $rule->updatedAt,
            ])->execute();

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateRule($name, $rule)
    {
        if ($rule->name !== $name && !$this->supportsCascadeUpdate()) {
            $this->db->createCommand()
                ->update($this->itemTable, ['rule_name' => $rule->name], ['rule_name' => $name])
                ->execute();
        }

        $rule->updatedAt = time();

        $this->db->createCommand()
            ->update($this->ruleTable, [
                'name' => $rule->name,
                'data' => serialize($rule),
                'updated_at' => $rule->updatedAt,
            ], [
                'name' => $name,
            ])->execute();

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function removeRule($rule)
    {
        if (!$this->supportsCascadeUpdate()) {
            $this->db->createCommand()
                ->update($this->itemTable, ['rule_name' => null], ['rule_name' => $rule->name])
                ->execute();
        }

        $this->db->createCommand()
            ->delete($this->ruleTable, ['name' => $rule->name])
            ->execute();

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getItems($type)
    {
        $query = (new Query())
            ->from($this->itemTable)
            ->where(['type' => $type]);

        $items = [];
        foreach ($query->all($this->db) as $row) {
            $items[$row['name']] = $this->populateItem($row);
        }

        return $items;
    }

    /**
     * Populates an auth item with the data fetched from database.
     * @param array $row the data from the auth item table
     * @return Item the populated auth item instance (either Role or Permission)
     */
    protected function populateItem($row)
    {
        $class = $row['type'] == Item::TYPE_PERMISSION ? Permission::className() : Role::className();

        if (!isset($row['data']) || ($data = @unserialize(is_resource($row['data']) ? stream_get_contents($row['data']) : $row['data'])) === false) {
            $data = null;
        }

        return new $class([
            'name' => $row['name'],
            'type' => $row['type'],
            'description' => $row['description'],
            'ruleName' => $row['rule_name'] ?: null,
            'data' => $data,
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ]);
    }

    /**
     * {@inheritdoc}
     * The roles returned by this method include the roles assigned via [[$defaultRoles]].
     */
    public function getRolesByUser($userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return [];
        }

        $query = (new Query())->select('b.*')
            ->from(['a' => $this->assignmentTable, 'b' => $this->itemTable])
            ->where('{{a}}.[[item_name]]={{b}}.[[name]]')
            ->andWhere(['a.user_id' => (string) $userId])
            ->andWhere(['b.type' => Item::TYPE_ROLE]);

        $roles = $this->getDefaultRoleInstances();
        foreach ($query->all($this->db) as $row) {
            $roles[$row['name']] = $this->populateItem($row);
        }

        return $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildRoles($roleName)
    {
        $role = $this->getRole($roleName);

        if ($role === null) {
            throw new InvalidArgumentException("Role \"$roleName\" not found.");
        }

        $result = [];
        $this->getChildrenRecursive($roleName, $this->getChildrenList(), $result);

        $roles = [$roleName => $role];

        $roles += array_filter($this->getRoles(), function (Role $roleItem) use ($result) {
            return array_key_exists($roleItem->name, $result);
        });

        return $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionsByRole($roleName)
    {
        $childrenList = $this->getChildrenList();
        $result = [];
        $this->getChildrenRecursive($roleName, $childrenList, $result);
        if (empty($result)) {
            return [];
        }
        $query = (new Query())->from($this->itemTable)->where([
            'type' => Item::TYPE_PERMISSION,
            'name' => array_keys($result),
        ]);
        $permissions = [];
        foreach ($query->all($this->db) as $row) {
            $permissions[$row['name']] = $this->populateItem($row);
        }

        return $permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionsByUser($userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return [];
        }

        $directPermission = $this->getDirectPermissionsByUser($userId);
        $inheritedPermission = $this->getInheritedPermissionsByUser($userId);

        return array_merge($directPermission, $inheritedPermission);
    }

    /**
     * Returns all permissions that are directly assigned to user.
     * @param string|int $userId the user ID (see [[\yii\web\User::id]])
     * @return Permission[] all direct permissions that the user has. The array is indexed by the permission names.
     * @since 2.0.7
     */
    protected function getDirectPermissionsByUser($userId)
    {
        $query = (new Query())->select('b.*')
            ->from(['a' => $this->assignmentTable, 'b' => $this->itemTable])
            ->where('{{a}}.[[item_name]]={{b}}.[[name]]')
            ->andWhere(['a.user_id' => (string) $userId])
            ->andWhere(['b.type' => Item::TYPE_PERMISSION]);

        $permissions = [];
        foreach ($query->all($this->db) as $row) {
            $permissions[$row['name']] = $this->populateItem($row);
        }

        return $permissions;
    }

    /**
     * Returns all permissions that the user inherits from the roles assigned to him.
     * @param string|int $userId the user ID (see [[\yii\web\User::id]])
     * @return Permission[] all inherited permissions that the user has. The array is indexed by the permission names.
     * @since 2.0.7
     */
    protected function getInheritedPermissionsByUser($userId)
    {
        $query = (new Query())->select('item_name')
            ->from($this->assignmentTable)
            ->where(['user_id' => (string) $userId]);

        $childrenList = $this->getChildrenList();
        $result = [];
        foreach ($query->column($this->db) as $roleName) {
            $this->getChildrenRecursive($roleName, $childrenList, $result);
        }

        if (empty($result)) {
            return [];
        }

        $query = (new Query())->from($this->itemTable)->where([
            'type' => Item::TYPE_PERMISSION,
            'name' => array_keys($result),
        ]);
        $permissions = [];
        foreach ($query->all($this->db) as $row) {
            $permissions[$row['name']] = $this->populateItem($row);
        }

        return $permissions;
    }

    /**
     * Returns the children for every parent.
     * @return array the children list. Each array key is a parent item name,
     * and the corresponding array value is a list of child item names.
     */
    protected function getChildrenList()
    {
        $query = (new Query())->from($this->itemChildTable);
        $parents = [];
        foreach ($query->all($this->db) as $row) {
            $parents[$row['parent']][] = $row['child'];
        }

        return $parents;
    }

    /**
     * Recursively finds all children and grand children of the specified item.
     * @param string $name the name of the item whose children are to be looked for.
     * @param array $childrenList the child list built via [[getChildrenList()]]
     * @param array $result the children and grand children (in array keys)
     */
    protected function getChildrenRecursive($name, $childrenList, &$result)
    {
        if (isset($childrenList[$name])) {
            foreach ($childrenList[$name] as $child) {
                $result[$child] = true;
                $this->getChildrenRecursive($child, $childrenList, $result);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRule($name)
    {
        if ($this->rules !== null) {
            return isset($this->rules[$name]) ? $this->rules[$name] : null;
        }

        $row = (new Query())->select(['data'])
            ->from($this->ruleTable)
            ->where(['name' => $name])
            ->one($this->db);
        if ($row === false) {
            return null;
        }
        $data = $row['data'];
        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }

        return unserialize($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getRules()
    {
        if ($this->rules !== null) {
            return $this->rules;
        }

        $query = (new Query())->from($this->ruleTable);

        $rules = [];
        foreach ($query->all($this->db) as $row) {
            $data = $row['data'];
            if (is_resource($data)) {
                $data = stream_get_contents($data);
            }
            $rules[$row['name']] = unserialize($data);
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignment($roleName, $userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return null;
        }

        $row = (new Query())->from($this->assignmentTable)
            ->where(['user_id' => (string) $userId, 'item_name' => $roleName])
            ->one($this->db);

        if ($row === false) {
            return null;
        }

        return new Assignment([
            'userId' => $row['user_id'],
            'roleName' => $row['item_name'],
            'createdAt' => $row['created_at'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignments($userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return [];
        }

        $query = (new Query())
            ->from($this->assignmentTable)
            ->where(['user_id' => (string) $userId]);

        $assignments = [];
        foreach ($query->all($this->db) as $row) {
            $assignments[$row['item_name']] = new Assignment([
                'userId' => $row['user_id'],
                'roleName' => $row['item_name'],
                'createdAt' => $row['created_at'],
            ]);
        }

        return $assignments;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function canAddChild($parent, $child)
    {
        return !$this->detectLoop($parent, $child);
    }

    /**
     * {@inheritdoc}
     */
    public function addChild($parent, $child)
    {
        if ($parent->name === $child->name) {
            throw new InvalidArgumentException("Cannot add '{$parent->name}' as a child of itself.");
        }

        if ($parent instanceof Permission && $child instanceof Role) {
            throw new InvalidArgumentException('Cannot add a role as a child of a permission.');
        }

        if ($this->detectLoop($parent, $child)) {
            throw new InvalidCallException("Cannot add '{$child->name}' as a child of '{$parent->name}'. A loop has been detected.");
        }

        $this->db->createCommand()
            ->insert($this->itemChildTable, ['parent' => $parent->name, 'child' => $child->name])
            ->execute();

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild($parent, $child)
    {
        $result = $this->db->createCommand()
                ->delete($this->itemChildTable, ['parent' => $parent->name, 'child' => $child->name])
                ->execute() > 0;

        $this->invalidateCache();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChildren($parent)
    {
        $result = $this->db->createCommand()
                ->delete($this->itemChildTable, ['parent' => $parent->name])
                ->execute() > 0;

        $this->invalidateCache();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChild($parent, $child)
    {
        return (new Query())
                ->from($this->itemChildTable)
                ->where(['parent' => $parent->name, 'child' => $child->name])
                ->one($this->db) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($name)
    {
        $query = (new Query())
            ->select(['name', 'type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'])
            ->from([$this->itemTable, $this->itemChildTable])
            ->where(['parent' => $name, 'name' => new Expression('[[child]]')]);

        $children = [];
        foreach ($query->all($this->db) as $row) {
            $children[$row['name']] = $this->populateItem($row);
        }

        return $children;
    }

    /**
     * Checks whether there is a loop in the authorization item hierarchy.
     * @param Item $parent the parent item
     * @param Item $child the child item to be added to the hierarchy
     * @return bool whether a loop exists
     */
    protected function detectLoop($parent, $child)
    {
        if ($child->name === $parent->name) {
            return true;
        }
        foreach ($this->getChildren($child->name) as $grandchild) {
            if ($this->detectLoop($parent, $grandchild)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll()
    {
        $this->removeAllAssignments();
        $this->db->createCommand()->delete($this->itemChildTable)->execute();
        $this->db->createCommand()->delete($this->itemTable)->execute();
        $this->db->createCommand()->delete($this->ruleTable)->execute();
        $this->invalidateCache();
    }

    /**
     * {@inheritdoc}
     */
    public function removeAllPermissions()
    {
        $this->removeAllItems(Item::TYPE_PERMISSION);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAllRoles()
    {
        $this->removeAllItems(Item::TYPE_ROLE);
    }

    /**
     * Removes all auth items of the specified type.
     * @param int $type the auth item type (either Item::TYPE_PERMISSION or Item::TYPE_ROLE)
     */
    protected function removeAllItems($type)
    {
        if (!$this->supportsCascadeUpdate()) {
            $names = (new Query())
                ->select(['name'])
                ->from($this->itemTable)
                ->where(['type' => $type])
                ->column($this->db);
            if (empty($names)) {
                return;
            }
            $key = $type == Item::TYPE_PERMISSION ? 'child' : 'parent';
            $this->db->createCommand()
                ->delete($this->itemChildTable, [$key => $names])
                ->execute();
            $this->db->createCommand()
                ->delete($this->assignmentTable, ['item_name' => $names])
                ->execute();
        }
        $this->db->createCommand()
            ->delete($this->itemTable, ['type' => $type])
            ->execute();

        $this->invalidateCache();
    }

    /**
     * {@inheritdoc}
     */
    public function removeAllRules()
    {
        if (!$this->supportsCascadeUpdate()) {
            $this->db->createCommand()
                ->update($this->itemTable, ['rule_name' => null])
                ->execute();
        }

        $this->db->createCommand()->delete($this->ruleTable)->execute();

        $this->invalidateCache();
    }

    /**
     *
     */
    public function loadFromCache()
    {
        if ($this->items !== null || !$this->cache instanceof CacheInterface) {
            return;
        }

        $data = $this->cache->get($this->cacheKey);
        if (is_array($data) && isset($data[0], $data[1], $data[2])) {
            list($this->items, $this->rules, $this->parents) = $data;
            return;
        }

        $query = (new Query())->from($this->itemTable);
        $this->items = [];
        foreach ($query->all($this->db) as $row) {
            $this->items[$row['name']] = $this->populateItem($row);
        }

        $query = (new Query())->from($this->ruleTable);
        $this->rules = [];
        foreach ($query->all($this->db) as $row) {
            $data = $row['data'];
            if (is_resource($data)) {
                $data = stream_get_contents($data);
            }
            $this->rules[$row['name']] = unserialize($data);
        }

        $query = (new Query())->from($this->itemChildTable);
        $this->parents = [];
        foreach ($query->all($this->db) as $row) {
            if (isset($this->items[$row['child']])) {
                $this->parents[$row['child']][] = $row['parent'];
            }
        }

        $this->cache->set($this->cacheKey, [$this->items, $this->rules, $this->parents]);
    }

    /**
     * Returns all role assignment information for the specified role.
     * @param string $roleName
     * @return string[] the ids. An empty array will be
     * returned if role is not assigned to any user.
     * @since 2.0.7
     */
    public function getUserIdsByRole($roleName)
    {
        if (empty($roleName)) {
            return [];
        }

        return (new Query())->select('[[user_id]]')
            ->from($this->assignmentTable)
            ->where(['item_name' => $roleName])->column($this->db);
    }

    /**
     * Check whether $userId is empty.
     * @param mixed $userId
     * @return bool
     */
    private function isEmptyUserId($userId)
    {
        return !isset($userId) || $userId === '';
    }



}
