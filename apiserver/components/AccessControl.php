<?php

namespace apiserver\components;

use yii\web\ForbiddenHttpException;

class AccessControl extends \common\components\AccessControl
{
    /**
     * Denies the access of the user.
     * The default implementation will redirect the user to the login page if he is a guest;
     * if the user is already logged, a 403 HTTP exception will be thrown.
     * @param  User $user the current user
     * @throws ForbiddenHttpException if the user is already logged in.
     */
    protected function denyAccess($user = null)
    {
        throw new ForbiddenHttpException(\Yii::t('yii', 'You are not allowed to perform this action.'));//
    }

}