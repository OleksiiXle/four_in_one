<?php

/* @var $this yii\web\View */
/* @var $user common\models\User */

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['adminxx/user/invitation-confirm', 'token' => $user->email_confirm_token]);
?>
Hello <?= $user->username ?>,

Follow the link below to signup on site .......:

<?= $verifyLink ?>

Your password:

<?= $user->password ?>
