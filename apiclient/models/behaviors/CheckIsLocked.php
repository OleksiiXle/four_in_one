<?php

namespace app\models\behaviors;


trait CheckIsLocked
{
    public function isFreeLock($id)
    {
        $isFreeLock = \Yii::$app->db->createCommand('SELECT IS_FREE_LOCK("'. $id .'")')->queryScalar();

        return ($isFreeLock == 1);
    }

    public function getLock($id, $seconds = 7200)
    {
        $getLock = \Yii::$app->db->createCommand('SELECT GET_LOCK("'. $id .'", ' . $seconds . ')')->queryScalar();

        return ($getLock == 1);
    }

    public function releaseLock($id, $seconds = 7200)
    {
        $releaseLock = \Yii::$app->db->createCommand('SELECT RELEASE_LOCK("'. $id .'")')->queryScalar();

        return ($releaseLock == 1);
    }


}