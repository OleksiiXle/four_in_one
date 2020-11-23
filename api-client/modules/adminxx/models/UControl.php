<?php

namespace app\modules\adminxx\models;

use app\helpers\Functions;
use yii\db\Exception;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "u_control".
 *
 * @property int $user_id
 * @property string $remote_ip
 * @property string $referrer
 * @property string $remote_host
 * @property string $absolute_url
 * @property string $url
 * @property int $created_at
 */
class UControl extends ActiveRecord
{
    const RECORDS_LIMIT = 10000; //-- размер БД после которого инфа о незарегистрированных гостях не пишется
    private $_createdAt;
    private $_updatedAt;
    public $_username;
    public $_userData;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'u_control';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['referrer', 'absolute_url', 'url'], 'string'],
            [['remote_ip', 'remote_host'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ident',
            'user_id' => 'User ID',
            'remote_ip' => 'Remote Ip',
            'username' => 'Login',
            'referrer' => 'Referrer',
            'remote_host' => 'Remote Host',
            'absolute_url' => 'Absolute Url',
            'url' => 'Останній роут',
            'created_at' => 'time',
            'updated_at' => 'time',
            'createdAt' => 'Перший візіт',
            'updatedAt' => 'Останній візіт',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSameIp()
    {
        return $this->hasMany(self::class, ['remote_ip' => 'remote_ip'])
            ->andWhere('!=', 'user_id', $this->user_id)

            ;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserDatas()
    {
        return $this->hasOne(UserData::class, ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserDepartments()
    {
        return $this->hasMany(UserData::class, ['user_id' => 'user_id']);
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        $this->_createdAt = (isset($this->created_at)) ? Functions::intToDateTime($this->created_at) : '';
        return $this->_createdAt;
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        $this->_updatedAt = (isset($this->updated_at)) ? Functions::intToDateTime($this->updated_at) : '';
        return $this->_updatedAt;
    }

    /**
     * @return string
     */
    public function getUserData()
    {
        $this->_userData = '';
        $userDatas = $this->userDatas;
        if (isset($userDatas)){
            $this->_userData = $userDatas->UserFio . ' ' . $userDatas->gunp;
        }
        return $this->_userData;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(UserM::class, ['id' => 'user_id']);
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        $this->_username = '';
        if (!empty($this->user)){
            $this->_username = $this->user->username;
        }
        return $this->_username;
    }

    /**
     * Фиксация визита зарегистрированных пользователей и гостей
     * @return string
     */
    public static function guestsAndUsersControl($guestControl, $userControl)
    {
        try{
            $request = \Yii::$app->getRequest();
            $userId = \Yii::$app->user->getId();
            $user_id = (!empty($userId)) ? $userId : 0;
            $url = $request->getUrl();
            $remote_ip = $request->getRemoteIP() ;
            $remote_ip = (!empty($remote_ip)) ? trim($remote_ip) : 'none';
            $visitTime = time();
            if (!$guestControl){
                //---- фиксируем только зарегистрированных пользователей
                $alreadyExists = (new \yii\db\Query)
                    ->from('u_control')
                    ->where(['user_id' => $user_id])
                    ->count();
                if ($alreadyExists){
                    $strSql = "
                          UPDATE `u_control`
                              SET 
                                `url`='$url', `remote_ip`='$remote_ip', `updated_at`=$visitTime
                              WHERE user_id = '$user_id'
                        ";
                    $ret = \Yii::$app->db->createCommand($strSql)->execute();
                } else {
                    $strSql = "
                         INSERT INTO `u_control`
                            (`user_id`, `remote_ip`, `url`, `created_at`, `updated_at`) 
                            VALUES 
                            ($user_id, '$remote_ip', '$url', $visitTime, $visitTime )
                             ";
                    $ret = \Yii::$app->db->createCommand($strSql)->execute();
                }
            } else {
                //---- фиксируем зарегистрированных пользователей и гостей
                if ($user_id == 0){
                    $referrer = $request->getReferrer();
                    $remote_host = $request->getRemoteHost();
                    $absolute_url = $request->getAbsoluteUrl();

                    $countControl = (new \yii\db\Query)
                        ->from('u_control')
                        ->count();
                    if ($countControl < self::RECORDS_LIMIT){
                        //-- если юсер не зарегистрированный пользователь, проверяем заполнение БД
                        $alreadyExists = (new \yii\db\Query)
                            ->from('u_control')
                            ->where(['remote_ip' => $remote_ip, 'user_id' => 0])
                            ->count();
                        if ($alreadyExists){
                            $strSql = "
                          UPDATE `u_control`
                              SET 
                                `user_id`=$user_id, `referrer`= '$referrer', `remote_host`='$remote_host', 
                                `absolute_url`='$absolute_url', `url`='$url', `updated_at`=$visitTime
                              WHERE (remote_ip = '$remote_ip') AND (user_id = 0)
                        ";
                            $ret = \Yii::$app->db->createCommand($strSql)->execute();
                        } else {
                            $strSql = "
                         INSERT INTO `u_control`
                            (`user_id`, `remote_ip`, `referrer`, `remote_host`, `absolute_url`, `url`, `created_at`, `updated_at`) 
                            VALUES 
                            ($user_id, '$remote_ip', '$referrer', '$remote_host', '$absolute_url', '$url', $visitTime, $visitTime )
                             ";
                            $ret = \Yii::$app->db->createCommand($strSql)->execute();
                        }
                    }
                } else {
                    $alreadyExists = (new \yii\db\Query)
                        ->from('u_control')
                        ->where(['user_id' => $user_id])
                        ->count();
                    if ($alreadyExists){
                        $strSql = "
                          UPDATE `u_control`
                              SET 
                               `url`='$url', `remote_ip`='$remote_ip', `updated_at`=$visitTime
                              WHERE user_id = '$user_id'
                        ";
                        $ret = \Yii::$app->db->createCommand($strSql)->execute();
                    } else {
                        $strSql = "
                         INSERT INTO `u_control`
                            (`user_id`, `url`, `remote_ip`, `created_at`, `updated_at`) 
                            VALUES 
                            ($user_id, '$url', '$remote_ip', $visitTime, $visitTime )
                             ";
                        $ret = \Yii::$app->db->createCommand($strSql)->execute();
                    }
                }

            }

            return '';
        } catch (Exception $e){
            return $e->getMessage();
        }
    }

    public static function clearOldRecords()
    {
        try {
            $guestControlDuration = \Yii::$app->params['guestControlDuration'];
            $tExpire = time() - $guestControlDuration;
            $ret = 0;

            //-- удаляем устаревшие записи
            if ($guestControlDuration > 0){
                $strSql = "DELETE FROM u_control WHERE (updated_at < $tExpire);";
               // $strSql = "SELECT count(*) AS cnt FROM u_control WHERE (updated_at_ < $tExpire);";
                $ret = \Yii::$app->db->createCommand($strSql)->execute();
            }
        } catch (Exception $e){
            $ret = $e->getMessage();
        }
        return $ret;

    }

    /**
     * Возвращает массив/количество идентификаторов измененных текущим пользователем подразделений, должностей , персонала
     *
     * @param string $target  - что изменялось - 'Personal'/'Position'/'Department'
     * @param string $operationType  - вид изменений - 'created'/'updated'/'deleted'
     * @param int $interval - временной интервал в timestamp от текущего времени, если 0 - то все изменения
     * @param boolean $retArray - если true - возвращает массив, если нет - возвращает количество
     * @return array/int
     */
    public function getChangedItems($target, $operationType, $retArray=true, $interval = 0)
    {
        switch ($target){
            case 'Personal':
                $query =  PersonalCommon::find();
                break;
            case 'Position':
                $query =  PositionCommon::find();
                break;
            case 'Department':
                $query =  DepartmentCommon::find();
                break;
        }
        $queryDeleted = false;
        switch ($operationType){
            case ('created'):
                $queryUser = 'creation_user';
                $queryTime = 'creation_time';
                break;
            case ('deleted'):
                $queryDeleted = true;
            case ('updated'):
                $queryUser = 'update_user';
                $queryTime = 'update_time';
                break;
        }
        if (isset($query)){
            $query
                ->where([$queryUser => $this->user_id]);
            if ($interval > 0){
                $query->andWhere("$queryTime > $interval");
            }
            if ($queryDeleted){
                $query->andWhere("deleted='1'");
            } else {
                $query->andWhere("deleted='0'");
            }
            if ($retArray){
                $ret = [];
                $data = $query
                    ->asArray()
                    ->all();
                foreach ($data as $d){
                    $ret[$d['id']] = [
                        'id' =>   $d['id'],
                        'operation' =>   $operationType,
                    ];
                }
            } else {
                $ret = $query->count();
            }
            //$r = $query->createCommand()->getSql();
            return $ret;
        }
        return ($retArray ? [] : 0);
    }

    /**
     * Возвращает строку для грида создано/изменено/удалено текущим пользователем подразделений, должностей , персонала
     * за временной интервал
     * @param string $target - что изменялось - 'Personal'/'Position'/'Department'
     * @param int $interval - временной интервал в timestamp от текущего времени, если 0 - то все изменения
     * @return string
     */
    public function getCgangedItemsCount($target, $interval = 0){
        $countCreated = $this->getChangedItems($target, 'created', false, $interval );
        $countUpdatet = $this->getChangedItems($target, 'updated', false, $interval );
        $countDeleted = $this->getChangedItems($target, 'deleted', false, $interval );
        return $countCreated . '/' . $countUpdatet . '/' . $countDeleted;
    }
}
