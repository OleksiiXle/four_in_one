<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\web\NotFoundHttpException;

class UserProfileComponent extends Component
{
    public $profileData = null;
    public $userIdData = null;

    public $languageData = null;
    public $testAttributeData = null;

    const USER_PROFILE_SESSION_ATTRIBUTE = 'userProfile';
    const ATTRIBUTES = [
        'language' => 'en-US',
        'testAttribute' => 'Some test value',
    ];


    public function __get($name)
    {
        $tmp = 1;
        switch ($name) {
            case 'userId':
                if ($this->userIdData === null) {
                    $this->userIdData = Yii::$app->user->getId();
                }

                return $this->userIdData;
                break;
            case 'profile':
                if ($this->profileData === null) {
                    $complete = true;
                    $session = \Yii::$app->session;
                    $userProfile = $session->get(self::USER_PROFILE_SESSION_ATTRIBUTE, []);
                    if (empty($userProfile) && !Yii::$app->user->isGuest) {
                        $userProfileJSON = Yii::$app->db
                            ->createCommand("SELECT profile FROM user_data WHERE user_id=$this->userId;")
                            ->queryScalar();
                        if (!empty($userProfileJSON)){
                            $userProfile = json_decode($userProfileJSON, true);
                        }
                    }
                    if (empty($userProfile)) {
                        //-- профиля нигде нет
                        foreach (self::ATTRIBUTES as $attribute => $defaultValue) {
                            $this->{$attribute . 'Data'} = $userProfile[$attribute] = $defaultValue;
                        }
                        $this->profile = $userProfile;
                        return $userProfile;
                    } else {
                        //-- профиль нашелся
                        foreach (self::ATTRIBUTES as $attribute => $defaultValue) {
                            if (empty($userProfile[$attribute])) {
                                $complete = false;
                                $this->{$attribute . 'Data'} = $userProfile[$attribute] = $defaultValue;
                            } else {
                                $this->{$attribute . 'Data'} = $userProfile[$attribute];
                            }
                        }
                    }
                    if (!$complete) {
                        $this->profile = $userProfile;
                    }
                    return $userProfile;
                }

                return $this->profileData;
                break;
            default:
                if (!isset(self::ATTRIBUTES[$name])) {
                    throw new NotFoundHttpException("Attribute $name not found!!!");
                }
                if ($this->{$name . 'Data'} === null) {
                    $profile = $this->profile;

                    return $profile[$name];
                } else {
                    return $this->{$name . 'Data'};
                }
        }
    }

    public function __set($name, $value)
    {
        $tmp = 1;
        switch ($name) {
            case 'profile':
                $this->profileData = $value;
                if (!Yii::$app->user->isGuest) {
                    $userProfileJSON = json_encode($value);
                    $ret = \Yii::$app->db
                        ->createCommand("UPDATE user_data SET profile = '$userProfileJSON' WHERE user_id=$this->userId;")
                        ->execute();
                }
                $session = \Yii::$app->session;
                $session->set(self::USER_PROFILE_SESSION_ATTRIBUTE, $value);
                break;
            default:
                if (!isset(self::ATTRIBUTES[$name])) {
                    throw new NotFoundHttpException("Attribute $name not found!!!");
                }
                $profile = $this->profile;
                $this->{$name . 'Data'} = $profile[$name] = $value;
                $this->profile = $profile;
        }
    }

    public function getProfileFromDb()
    {
        $tmp = 1;
        if (!Yii::$app->user->isGuest) {
            $userProfileJSON = Yii::$app->db
                ->createCommand("SELECT profile FROM user_data WHERE user_id=$this->userId;")
                ->queryScalar();
            if (!empty($userProfileJSON)){
                $userProfile = json_decode($userProfileJSON, true);
            } else {
                foreach (self::ATTRIBUTES as $attribute => $defaultValue) {
                    $this->{$attribute . 'Data'} = $userProfile[$attribute] = $defaultValue;
                }
            }
            $this->profile = $userProfile;
        }
    }
}