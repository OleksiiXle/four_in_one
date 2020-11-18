<?php

namespace common\components;

use Yii;
use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;
use yii\db\Connection;
use yii\di\Instance;
use yii\i18n\MessageSource;
use common\components\models\Translation;

/**
 * DbMessageSource extends [[MessageSource]] and represents a message source that stores translated
 * messages in database.
 *
 * The database must contain the following two tables: source_message and message.
 *
 * The `source_message` table stores the messages to be translated, and the `message` table stores
 * the translated messages. The name of these two tables can be customized by setting [[sourceMessageTable]]
 * and [[messageTable]], respectively.
 *
 * The database connection is specified by [[db]]. Database schema could be initialized by applying migration:
 *
 * ```
 * yii migrate --migrationPath=@yii/i18n/migrations/
 * ```
 *
 * If you don't want to use migration and need SQL instead, files for all databases are in migrations directory.
 *
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
class DbMessageSource extends MessageSource
{
    /*
     * \Yii::$app->language = ru-RU

     translateMessage($category = 'app', $message = 'sdfsdf', $language'ru-RU')
         вызывает loadMessages($category = 'app', $language = 'ru-RU')
         loadMessages возвращает
    Array
(
    [ and ] =>  и
    ["{attribute}" does not support operator "{operator}".] => "{attribute}" не поддерживает оператор "{operator}".
    [(not set)] => (не задано)
    [An internal server error occurred.] => Возникла внутренняя ошибка сервера.
    [Are you sure you want to delete this item?] => Вы уверены, что хотите удалить этот элемент?
    [Condition for "{attribute}" should be either a value or valid operator specification.] => Условие для "{attribute}" должно быть или значением или верной спецификацией оператора.
    [Delete] => Удалить
    [Error] => Ошибка
    [File upload failed.] => Загрузка файла не удалась.
    [Home] => Главная
    [Invalid data received for parameter "{param}".] => Неправильное значение параметра "{param}".
    [Login Required] => Требуется вход.
    [Missing required arguments: {params}] => Отсутствуют обязательные аргументы: {params}
    [Missing required parameters: {params}] => Отсутствуют обязательные параметры: {params}
    [No] => Нет
)
    $this->_messages = array


    translateMessage возвращает $this->_messages['key']

         */


    /**
     * Prefix which would be used when generating cache key.
     * @deprecated This constant has never been used and will be removed in 2.1.0.
     */
    const CACHE_KEY_PREFIX = 'DbMessageSource';

    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     *
     * After the DbMessageSource object is created, if you want to change this property, you should only assign
     * it with a DB connection object.
     *
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db = 'db';
    /**
     * @var CacheInterface|array|string the cache object or the application component ID of the cache object.
     * The messages data will be cached using this cache object.
     * Note, that to enable caching you have to set [[enableCaching]] to `true`, otherwise setting this property has no effect.
     *
     * After the DbMessageSource object is created, if you want to change this property, you should only assign
     * it with a cache object.
     *
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     * @see cachingDuration
     * @see enableCaching
     */
    public $cache = 'cache';
    /**
     * @var string the name of the source message table.
     */
    public $sourceMessageTable = '{{%source_message}}';
    /**
     * @var string the name of the translated message table.
     */
    public $messageTable = '{{%message}}';
    /**
     * @var int the time in seconds that the messages can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire.
     * @see enableCaching
     */
    public $cachingDuration = 0;
    /**
     * @var bool whether to enable caching translated messages
     */
    public $enableCaching = true;


    /**
     * Initializes the DbMessageSource component.
     * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
     * Configured [[cache]] component would also be initialized.
     * @throws InvalidConfigException if [[db]] is invalid or [[cache]] is invalid.
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::class);
        if ($this->enableCaching) {
            $this->cache = Instance::ensure($this->cache, 'yii\caching\CacheInterface');
        }
        /*
        $conserveLanguage = \Yii::$app->conservation->language;
        if (!Yii::$app->user->isGuest &&  !empty($conserveLanguage)){
            $this->sourceLanguage = $conserveLanguage;
        }
        */
    }

    /**
     * Loads the message translation for the specified language and category.
     * If translation for specific locale code such as `en-US` isn't found it
     * tries more generic `en`.
     *
     * @param string $category the message category
     * @param string $language the target language
     * @return array the loaded messages. The keys are original messages, and the values
     * are translated messages.
     */
    protected function loadMessages($category, $language)
    {
        $t=1;
        if ($this->enableCaching) {
            $key = [
                __CLASS__,
                $category,
                $language,
            ];
            $messages = $this->cache->get($key);
            if ($messages === false) {
                $messages = $this->loadMessagesFromDb($category, $language);
                $this->cache->set($key, $messages, $this->cachingDuration);
            }

            return $messages;
        }

        return $this->loadMessagesFromDb($category, $language);
    }

    /**
     * Loads the messages from database.
     * You may override this method to customize the message storage in the database.
     * @param string $category = 'app'/'yii'.
     * @param string $language the target language. = 'ru-RU'/ 'en-US' / ukr
     * $this->sourceLanguage = en-US
     * @return array the messages loaded from database.
    Array
    (
    [ and ] =>  и
    ["{attribute}" does not support operator "{operator}".] => "{attribute}" не поддерживает оператор "{operator}".
    [(not set)] => (не задано)
    [An internal server error occurred.] => Возникла внутренняя ошибка сервера.
    [Are you sure you want to delete this item?] => Вы уверены, что хотите удалить этот элемент?
    [Condition for "{attribute}" should be either a value or valid operator specification.] => Условие для "{attribute}" должно быть или значением или верной спецификацией оператора.
    [Delete] => Удалить
    [Error] => Ошибка
    )
     */
    protected function loadMessagesFromDb($category, $language)
    {
        $ret = Translation::getDictionary($category, $language);
        return $ret;
    }

    public function refreshCache($category)
    {
        $retDel = $retAdd = true;
        $languages = array_keys(Translation::getLanguages());
        foreach ($languages as $language){
            $key = [
                __CLASS__,
                $category,
                $language,
            ];
          //  $m1 = $this->cache->get($key);

            $retDel = $this->cache->delete($key);
            $messages = $this->loadMessagesFromDb($category, $language);
            $retAdd = $this->cache->set($key, $messages, $this->cachingDuration);
         //   $m2 = $this->cache->get($key);

        }
        return ($retDel && $retAdd);

    }

}
