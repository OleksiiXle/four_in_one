<?php
namespace app\modules\adminxx\models\filters;

use app\modules\adminxx\models\UserData;
use app\modules\adminxx\models\UserM;
use yii\base\Model;

class UserActivityFilter extends Model
{
    public $activityInterval;

    public $username;
    public $userFam; //last_name


    public function rules()
    {
        return [
            [['activityInterval', ], 'integer'],
            [['username', 'first_name'], 'string', 'min' => 3, 'max' => 32],
            [['username'], 'match', 'pattern' => UserM::USER_PASSWORD_PATTERN,
                'message' => \Yii::t('app', UserM::USER_PASSWORD_ERROR_MESSAGE)],
            [['userFam'],  'match', 'pattern' => UserM::USER_NAME_PATTERN,
                'message' => \Yii::t('app', UserM::USER_NAME_ERROR_MESSAGE)],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'activityInterval' => \Yii::t('app', 'Изменить интервал'),
            'username' => \Yii::t('app', 'Логин'),
            'userFam' => \Yii::t('app', 'Фамилия'),
        ];
    }


    public function getQuery($params = null){

        $query = UserData::find();

        if (!empty($this->username)){
            $query->joinWith(['userM']);
        }

        if (!empty($this->username)){
            $query->andWhere(['LIKE', 'user.username', $this->username]);
        }

        if (!empty($this->activityInterval)) {
            $timeFix = time() - $this->activityInterval;
        } else {
            $timeFix = time() - 3600;
        }
        $query->andWhere(['>', 'user_data.last_rout_time', $timeFix]);

        $query->orderBy('user_data.last_rout_time DESC');
       // $r = $query->createCommand()->getSql();

        return $query;

    }
}