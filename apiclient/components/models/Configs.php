<?php

namespace app\components\models;

use Yii;
use app\models\MainModel;
use yii\base\Exception;

/**
 * This is the model class for table "configs".
 *
 * @property int $id
 * @property string $owner
 * @property int $type
 * @property string $name
 * @property string $content
 */
class Configs extends MainModel
{

    const CONTENT_PATTERN = '/^[A-Za-z0-9 -.@]+$/u'; //--маска для нимени

    private $cacheKey = 'config';

    public $adminEmail = null;
    public $userControl = null;
    public $guestControl = null;
    public $guestControlDuration = null;
    public $menuType = null;
    public $permCacheKey = null;
    public $permCacheKeyDuration = null;
    public $passwordResetTokenExpire = null;  //const PASSWORD_RESET_TOKEN_EXPIRE = 3600;
    public $userDefaultRole = null;  //const DEFAULT_ROLE = 'user';
    public $rbacCacheSource = null;
    public $signupWithoutEmailConfirm = null;

    const ITEMS_LIST = [
      'adminEmail',
      'userControl',
      'guestControl',
      'guestControlDuration',
      'menuType',
      'permCacheKey',
      'permCacheKeyDuration',
      'passwordResetTokenExpire',
      'userDefaultRole',
      'rbacCacheSource',
      'signupWithoutEmailConfirm',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'configs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['owner'], 'required'],
            [['owner'], 'string', 'max' => 255],
            [['rbacCacheSource'], 'string', 'max' => 15],
            [['content'], 'string'],
            [['name'], 'string', 'max' => 250],
            ['name', 'unique'],
            [['content',
                'adminEmail',
                'guestControlDuration',
                'menuType',
                'permCacheKey',
                'permCacheKeyDuration',
                'passwordResetTokenExpire',
                'userDefaultRole',
                'userControl',
                'guestControl',
                'signupWithoutEmailConfirm',
            ],  'match', 'pattern' => self::CONTENT_PATTERN],

            [['adminEmail'], 'email'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'owner' => Yii::t('app', 'Владелец'),
            'name' => Yii::t('app', 'Название'),
            'content' => Yii::t('app', 'Содержимое'),
            'adminEmail' => Yii::t('app', 'Email администратора системы'),
            'userControl' => Yii::t('app', 'Контролировать посещение сайта зарегистрированными пользователями'),
            'guestControl' => Yii::t('app', 'Контролировать посещение сайта гостями'),
            'guestControlDuration' => Yii::t('app', 'Время храненния лога гостей'),
            'menuType' => Yii::t('app', 'Тип меню сайта'),
            'permCacheKey' => Yii::t('app', 'Ключ кеша разрешений'),
            'permCacheKeyDuration' => Yii::t('app', 'Время хранения разрешений в кеше'),
            'passwordResetTokenExpire' => Yii::t('app', 'Время жизни токена сброса пароля'),
            'userDefaultRole' => Yii::t('app', 'Начальная роль нового зарегистрированного пользователя'),
            'rbacCacheSource' => Yii::t('app', 'Место для кеширования данных аутентификации'),
            'signupWithoutEmailConfirm' => Yii::t('app', 'Регистрация нового пользователя только через подтверждения по Email'),
        ];
    }

    public function getConfigs()
    {
       // $ret = \Yii::$app->cache->delete($this->cacheKey);
        $tmp=1;
      //  self::deleteAll(['IN', 'name', self::ITEMS_LIST ]);
        try{
            $data = \Yii::$app->cache->get($this->cacheKey);
            //-- если в кеше сохранены настройки - берем их оттуда
            if (is_array($data)) {
                $ok = true;
                foreach (self::ITEMS_LIST as $item){
                    if (isset($data[$item])){
                        $this->{$item} = $data[$item]; //** todo после переделки убрать
                    } else {
                        $ok=false;
                        break;
                    }
                }
                if ($ok){
                    return $data;
                }
            }
            //-- если в кеше настроек нет - пробуем прочитать их из бд и сохоанить в кеш
            $data = self::find()
                ->indexBy('name')
                ->asArray()
                ->all();
            if (is_array($data)) {
                $ok = true;
                $dataToCache = [];
                foreach (self::ITEMS_LIST as $item){
                    if (isset($data[$item])){
                        $this->{$item} = $data[$item]['content'];
                        $dataToCache[$item] = $data[$item]['content'];
                    } else {
                        $ok=false;
                        break;
                    }
                }
                if ($ok){
                    $ret = \Yii::$app->cache->set($this->cacheKey, $dataToCache);
                    return $dataToCache;
                }
            }
            //-- если в бд тоже нет настроек - очищаем бд и записываем туда настройки из params.php
            $dataDel  = self::deleteAll(['IN', 'name', self::ITEMS_LIST ]);
            $params = \Yii::$app->params;
            $dataToCache = [];
            foreach (self::ITEMS_LIST as $item){
                $this->{$item} = $params[$item];
                $dataToCache[$item] = $params[$item];
                $model = new self();
                $model->owner = 'admin';
                $model->name = $item;
                $model->content = $params[$item];
                if (!$model->save()){
                    $this->addErrors($model->getErrors());
                }
            }
            if ($model->hasErrors()) {
                return false;
            }
            $ret = \Yii::$app->cache->set($this->cacheKey, $dataToCache);

            return $dataToCache;
        } catch (\Exception $e){
            $this->addError('id', $e->getMessage());
        }

        return false;
    }

    public function setConfigs()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $dataToCache = [];
            foreach (self::ITEMS_LIST as $item){
                $target = self::findOne(['name' => $item]);
                if (empty($target)){
                    $target = new self();
                }
                $target->owner = 'admin';
                $target->name = $item;
                $target->content = $this->{$item};
                if (!$target->save()){
                    $transaction->rollBack();
                    $this->addErrors($target->getErrors());
                    return false;
                }
                $dataToCache[$item] = $this->{$item};
            }
            $transaction->commit();

            \Yii::$app->cache->delete($this->cacheKey);
            $ret = \Yii::$app->cache->set($this->cacheKey, $dataToCache);
            return $dataToCache;
        } catch (\Exception $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            $this->addError('id', $e->getMessage());
            return false;
        }
    }

    public static function dictionaryYes()
    {
        return [
            '1' => \Yii::t('app', 'Да'),
            '0' => \Yii::t('app', 'Нет'),
        ];
    }

    public static function dictionaryrRacCacheSource()
    {
        return [
            'session' => \Yii::t('app', 'Сессия'),
            'cache' => \Yii::t('app', 'Кеш сайта'),
        ];
    }

    public static function dictionaryMenu()
    {
        return [
            'vertical' => \Yii::t('app', 'Вертикальное'),
            'horizontal' => \Yii::t('app', 'Горизонтальное'),
        ];
    }

    public static function dictionaryDuration()
    {
        return [
            '0' => \Yii::t('app', 'Постоянно'),
            '3600' => \Yii::t('app', 'Час'),
            '86400' => \Yii::t('app', 'Сутки'),
            '2592000' => \Yii::t('app', 'Месяц'),
            '31104000' => \Yii::t('app', 'Год'),
        ];
    }

    public static function dictionaryDurationSec()
    {
        return [
            '0' => \Yii::t('app', 'Постоянно'),
            '360' => \Yii::t('app', 'Час'),
            '8640' => \Yii::t('app', 'Сутки'),
            '259200' => \Yii::t('app', 'Месяц'),
            '3110400' => \Yii::t('app', 'Год'),
        ];
    }

    public static function dictionaryRoles()
    {
        $ret = [];
        $roles = \Yii::$app->authManager->getRoles();
        foreach ($roles as $role){
            $ret[$role->name] = $role->name;
        }
        return $ret;
    }







}
