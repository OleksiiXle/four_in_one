<?php

namespace app\components\conservation;

use app\components\conservation\models\Conservation;
use yii\base\Component;
use yii\base\Exception;

class ConservationComponent extends Component
{
    const LIFE_TIME = 86400 * 15;

    public $currentUser = 0;
    public $conserves;
    public $content;

    public function init()
    {
        parent::init();
        $user_id = \Yii::$app->user->id;
        $this->currentUser = (!empty($user_id)) ? $user_id : 0;
    }

    public function showUser()
    {
        return $this->currentUser;
    }

    /**
     * Чтение всех консерв юсера из БД - возвращает массив консерв или нул
     * @return mixed|null
     * @throws Exception
     */
    public static function getConservesDB()
    {
        try {
            if (!(\Yii::$app->user->isGuest)) {
                $user_id = \Yii::$app->user->id;
                $conserve = Conservation::findOne($user_id);
                $currentContent = null;
                if (isset($conserve)) {
                    $currentContent = json_decode($conserve->conservation, true);
                }
                return $currentContent;
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }

    /**
     * Чтение одной консервы юсера из БД по имени консервы, возвращает значение консервы
     * @param $name
     * @return null
     * @throws Exception
     */
    public static function getConserveDB($name)
    {
        try {
            if (!(\Yii::$app->user->isGuest)) {
                $user_id = \Yii::$app->user->id;
                $conserve = Conservation::findOne($user_id);
                $result = null;
                if (isset($conserve)) {
                    $currentContent = json_decode($conserve->conservation, true);
                    $result = (isset($currentContent[$name]['data'])) ? $currentContent[$name]['data'] : null;
                }
                return $result;
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return null;
    }

    /**
     * Запись одной консервы юсера в БД входные данные $name, $content
     * @param $name
     * @param $content
     * @return bool
     */
    public static function setConserveDB($name, $content)
    {
        try {
            if (!(\Yii::$app->user->isGuest)){
                $contentEx = ['data' => $content, 'expiries' => time() + self::LIFE_TIME];
                $user_id = \Yii::$app->user->id;
                $conserve = Conservation::findOne($user_id);
                if (!isset($conserve)){
                    $conserve = new Conservation();
                    $conserve->user_id = $user_id;
                    $newContent = json_encode([$name => $contentEx]);
                } else {
                    if (isset($conserve->conservation)){
                        $currentContent = json_decode($conserve->conservation, true);
                        $currentContent[$name] = $contentEx;
                        $newContent = json_encode($currentContent);
                    } else {
                        $newContent = json_encode([$name => $contentEx]);
                    }
                }
                $conserve->conservation = $newContent;
                if ($conserve->save()){
                    return $conserve->conservation;
                } else {
                    $errStr = '';
                    foreach ($conserve->getErrors() as $key => $value) {
                        $errStr = $errStr . $key . ': ' . $value[0] . '<br>';
                    }
                    return $errStr;
                }
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * Запись одной консервы  проекта приказа юсера в БД входные данные $name, $content
     * @param $name
     * @param $content
     * @return bool
     */
    public static function setConserveOrderDB($order_id, $stored_id, $stored_type)
    {
        $content = [
            'stored_id' => $stored_id,
            'stored_type' => $stored_type,
            'expiries' => time() + self::LIFE_TIME,
        ];
        $user_id = \Yii::$app->user->id;
        $conserve = Conservation::findOne($user_id);
        if (!isset($conserve)){
            $conserve = new Conservation();
            $conserve->user_id = $user_id;
            $newContent = json_encode(['order_' . $order_id => $content]);

        } else {
            if (isset($conserve->conservation)){
                $currentContent = json_decode($conserve->conservation, true);
                $currentContent['order_' . $order_id] = $content;
                $newContent = json_encode($currentContent);
            } else {
                $currentContent['order_' . $order_id] = $content;
                $newContent = json_encode($currentContent);
            }
        }
        $conserve->conservation = $newContent;
        if ($conserve->save()){
            return $conserve->conservation;
        } else {
            $errStr = '';
            foreach ($conserve->getErrors() as $key => $value) {
                $errStr = $errStr . $key . ': ' . $value[0] . '<br>';
            }
            return $errStr;
        }
    }

    /**
     * Чтение одной консервы проекта приказа юсера из БД по имени консервы, возвращает значение консервы
     * @param $name
     * @return null
     * @throws Exception
     */
    public static function getConserveOrderDB($order_id)
    {
        try {
            if (!(\Yii::$app->user->isGuest)) {
                $user_id = \Yii::$app->user->id;
                $conserve = Conservation::findOne($user_id);
                $result = null;
                if (isset($conserve)) {
                    $currentContent = json_decode($conserve->conservation, true);
                    $result = (isset($currentContent['order_' . $order_id])) ? $currentContent['order_' . $order_id] : null;
                }
               // return $currentContent['order_' . $order_id];
                return $result;
            }
        } catch (Exception $e) {
            throw $e;
        }
        return null;
    }

    /**
     * Запись одной консервы  проекта приказа юсера в БД входные данные $name, $content
     * @param $name
     * @param $content
     * @return bool
     */
    public static function setConserveDepartmentDB($stored_id, $stored_type)
    {
        $content = [
            'stored_id' => $stored_id,
            'stored_type' => $stored_type,
            'expiries' => time() + self::LIFE_TIME,
        ];
        $user_id = \Yii::$app->user->id;
        $conserve = Conservation::findOne($user_id);
        if (!isset($conserve)){
            $conserve = new Conservation();
            $conserve->user_id = $user_id;
            $newContent = json_encode(['department'  => $content]);

        } else {
            if (isset($conserve->conservation)){
                $currentContent = json_decode($conserve->conservation, true);
                $currentContent['department' ] = $content;
                $newContent = json_encode($currentContent);
            } else {
                $currentContent['department' ] = $content;
                $newContent = json_encode($currentContent);
            }
        }
        $conserve->conservation = $newContent;
        if ($conserve->save()){
            return $conserve->conservation;
        } else {
            $errStr = '';
            foreach ($conserve->getErrors() as $key => $value) {
                $errStr = $errStr . $key . ': ' . $value[0] . '<br>';
            }
            return $errStr;
        }
    }

    /**
     * Чтение одной консервы проекта приказа юсера из БД по имени консервы, возвращает значение консервы
     * @param $name
     * @return null
     * @throws Exception
     */
    public static function getConserveDepartmentDB()
    {
        try {
            if (!(\Yii::$app->user->isGuest)) {
                $user_id = \Yii::$app->user->id;
                $conserve = Conservation::findOne($user_id);
                $result = null;
                if (isset($conserve)) {
                    $currentContent = json_decode($conserve->conservation, true);
                    $result = (isset($currentContent['department'])) ? $currentContent['department'] : null;
                }
               // return $currentContent['department'];
                return $result;
            }
        } catch (Exception $e) {
            throw $e;
        }
        return null;
    }

    /**
     * Запись одной консервы  грида в БД входные данные $name, $content
     * @param $name
     * @param $content
     * @return bool
     */
    public static function setConserveGridDB($gridName, $gridType, $value)
    {
        $firstContent = [
            'data' => [
                $gridType => $value
            ],
            'expiries' => time() + self::LIFE_TIME,
        ];

        $user_id = \Yii::$app->user->id;
        $conserve = Conservation::findOne($user_id);
        if (!isset($conserve)){
            $conserve = new Conservation();
            $conserve->user_id = $user_id;
            $newContent = json_encode([$gridName  => $firstContent]);
        } else {
            if (isset($conserve->conservation)){
                $currentContent = json_decode($conserve->conservation, true);
                $currentContent[$gridName]['data'][$gridType] = $value;
                $currentContent[$gridName]['expiries'] = time() + self::LIFE_TIME;
                $newContent = json_encode($currentContent);
            } else {
                $currentContent[$gridName ] = $firstContent;
                $newContent = json_encode($currentContent);
            }
        }
        $conserve->conservation = $newContent;
        if ($conserve->save()){
            return $conserve->conservation;
        } else {
            $errStr = '';
            foreach ($conserve->getErrors() as $key => $value) {
                $errStr = $errStr . $key . ': ' . $value[0] . '<br>';
            }
            return $errStr;
        }
    }

    /**
     * Чтение всех консерв грида из БД  возвращает массив консерв
     * @param $name
     * @return null
     * @throws Exception
     */
    public static function getConserveGridDB($gridName)
    {
        try {
            if (!(\Yii::$app->user->isGuest)) {
                $user_id = \Yii::$app->user->id;
                $conserve = Conservation::findOne($user_id);
                $result = null;
                if (isset($conserve)) {
                    $currentContent = json_decode($conserve->conservation, true);
                    $result = (isset($currentContent[$gridName])) ? $currentContent[$gridName] : null;
                }
               // return $currentContent['department'];
                return $result;
            }
        } catch (Exception $e) {
            throw $e;
        }
        return null;
    }

    /**
     * Запись всех консерв грида в БД входные данные - массив
     * @param $name
     * @param $content
     * @return bool
     */
    public static function setConserveGridDBAll($gridName, $gridData)
    {
        $content = [
            'data' => $gridData,
            'expiries' => time() + self::LIFE_TIME,
        ];
        $user_id = \Yii::$app->user->id;
        $conserve = Conservation::findOne($user_id);
        if (!isset($conserve)){
            $conserve = new Conservation();
            $conserve->user_id = $user_id;
        }
        $conserve->conservation = json_encode([$gridName  => $content]);
        if ($conserve->save()){
            return '';
        } else {
            $errStr = '';
            foreach ($conserve->getErrors() as $key => $value) {
                $errStr = $errStr . $key . ': ' . $value[0] . '<br>';
            }
            return $errStr;
        }
    }

    /**
     * Запись одной консервы  проекта приказа юсера в БД входные данные $name, $content
     * @param $name
     * @param $content
     * @return bool
     */
    public static function setConserveDepartmentDBtt($stored_id, $stored_type, $tree_id)
    {
        $content = [
            'stored_id' => $stored_id,
            'stored_type' => $stored_type,
            'expiries' => time() + self::LIFE_TIME,
        ];
        $keyName = 'department_' . $tree_id;
        $user_id = \Yii::$app->user->id;
        $conserve = Conservation::findOne($user_id);
        if (!isset($conserve)){
            $conserve = new Conservation();
            $conserve->user_id = $user_id;
            $newContent = json_encode([$keyName  => $content]);

        } else {
            if (isset($conserve->conservation)){
                $currentContent = json_decode($conserve->conservation, true);
                $currentContent[$keyName] = $content;
                $newContent = json_encode($currentContent);
            } else {
                $currentContent[$keyName ] = $content;
                $newContent = json_encode($currentContent);
            }
        }
        $conserve->conservation = $newContent;
        if ($conserve->save()){
            return $conserve->conservation;
        } else {
            $errStr = '';
            foreach ($conserve->getErrors() as $key => $value) {
                $errStr = $errStr . $key . ': ' . $value[0] . '<br>';
            }
            return $errStr;
        }
    }

    /**
     * Чтение одной консервы проекта приказа юсера из БД по имени консервы, возвращает значение консервы
     * @param $name
     * @return null
     * @throws Exception
     */
    public static function getConserveDepartmentDBtt($tree_id)
    {
        try {
            if (!(\Yii::$app->user->isGuest)) {
                $keyName = 'department_' . $tree_id;
                $user_id = \Yii::$app->user->id;
                $conserve = Conservation::findOne($user_id);
                $result = null;
                if (isset($conserve)) {
                    $currentContent = json_decode($conserve->conservation, true);
                    $result = (isset($currentContent[$keyName])) ? $currentContent[$keyName] : null;
                }
                // return $currentContent['department'];
                return $result;
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return null;
    }






    public static function setConserveDB_test($name, $content)
    {
        $contentEx = ['data' => $content, 'expiries' => time() + self::LIFE_TIME];
        $user_id = \Yii::$app->user->id;
        $conserve = Conservation::findOne($user_id);
        if (!isset($conserve)){
            $conserve = new Conservation();
            $conserve->user_id = $user_id;
            $newContent = json_encode([$name => $contentEx]);
        } else {
            if (isset($conserve->conservation)){
                $currentContent = json_decode($conserve->conservation, true);
                $currentContent[$name] = $contentEx;
                $newContent = json_encode($currentContent);
            } else {
                $newContent = json_encode([$name => $contentEx]);
            }
        }
        //   $conserve->expires = time() + self::LIFE_TIME;
        $conserve->conservation = $newContent;
        if ($conserve->save()){
            return $conserve->conservation;
        } else {
            $errStr = '';
            foreach ($conserve->getErrors() as $key => $value) {
                $errStr = $errStr . $key . ': ' . $value[0] . '<br>';
            }
            return $errStr;
        }
    }

    /**
     * Чтение консерв юсера из сессии и запись их в БД
     * @param $conserves
     * @return bool
     */
    public static function saveConserves()
    {
        try{
            $session = \Yii::$app->session;
            $conserves = $session->get('conserves');
            foreach ($conserves as $key => $value){
                self::setConserveDB($key, $value);
            }
            return $conserves;
        } catch (Exception $e){
            return null;
        }
    }

    /**
     * Чтение всех консерв юсера из БД и запись их в сессию
     * @return bool
     */
    public function readConservesToSession()
    {
        $session = \Yii::$app->session;
        $session->set('conserves', \Yii::$app->conservation->getConservesDB());
        return true;
    }

    /**
     * Запись консервы в сессию в сессию
     * @return bool
     */
    public function setConserve($name, $value)
    {
        $session = \Yii::$app->session;
        $conserves = $session->get('conserves');
        $conserves[$name] = $value;
        $session->set('conserves', $conserves);
        return true;
    }

    /**
     * Чтение консервы из сессии
     * @return bool
     */
    public function getConserve($name)
    {
        $session = \Yii::$app->session;
        if (!$session->has('conserves')){
            $this->readConservesToSession();
        }
        $conserves = $session->get('conserves');
        $res = (isset($conserves[$name])) ? $conserves[$name] : null;
        return $res;
    }

    /**
     * Чтение всех консерв из сессии
     * @return bool
     */
    public function getConserves()
    {
        $session = \Yii::$app->session;
        if (!$session->has('conserves')){
            $this->readConservesToSession();
        }
        $conserves = $session->get('conserves');
        return $conserves;
    }

    /**
     * Удаление одной консервы юсера из БД по имени консервы,     * @param $name
     * @return null
     * @throws Exception
     */
    public static function removeConserveDB($name)
    {
        try {
            if (!(\Yii::$app->user->isGuest)) {
                $user_id = \Yii::$app->user->id;
                $conserve = Conservation::findOne($user_id);
                $result = null;
                if (isset($conserve)) {
                    $currentContent = json_decode($conserve->conservation, true);
                    if (isset($currentContent[$name])){
                        unset($currentContent[$name]);
                    }
                }
                $session = \Yii::$app->session;
                $conserves = $session->get('conserves');
                $result = (isset($conserves[$name])) ? $conserves[$name] : null;
                if (isset($conserves[$name])){
                    unset($conserves[$name]);
                    $session->set('conserves', $conserves);
                }
                self::readConservesToSession();
                return $result;
            }
        } catch (Exception $e) {
            throw $e;
        }
        return null;
    }
    /**
     * Удаление всех консервы юсера из БД по имени консервы,     * @param $name
     * @return null
     * @throws Exception
     */
    public static function removeConservesDB()
    {
        try {
            if (!(\Yii::$app->user->isGuest)) {
                $user_id = \Yii::$app->user->id;
                $result = Conservation::deleteAll(['user_id' => $user_id]);
                self::readConservesToSession();
                return $result;
            }
        } catch (Exception $e) {
            throw $e;
        }
        return null;
    }
}