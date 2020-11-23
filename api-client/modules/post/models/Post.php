<?php

namespace app\modules\post\models;

use app\models\MainModel;
use app\modules\adminxx\models\UserM;
use Yii;

/**
 * This is the model class for table "post".
 *
 * @property int $id ID
 * @property int $user_id Владелец
 * @property int $target Цель
 * @property int $type Тип
 * @property string $name Название
 * @property resource $content Содержимое
 * @property int $created_at Создано
 * @property int $updated_at Изменено
 *
 * @property PostMedia[] $postMedia
 */
class Post extends MainModel
{
    const TYPE_FRONT = 1;
    const TYPE_TARGET = 2;

    private $_ownerLastName;
    private $_ownerUsername;
    private $_mainImage;

    private $_listImages;

    /**
     * @return mixed
     */
    public function getListImages()
    {
        $this->_listImages = [];
        foreach ($this->images as $item){
            $this->_listImages [$item->id] = $item->name;
        }
        return $this->_listImages;
    }

    private $_listLinks;

    /**
     * @return mixed
     */
    public function getListLinks()
    {
        $this->_listLinks = [];
        foreach ($this->links as $item){
            $this->_listLinks [$item->id] = $item->name;
        }
        return $this->_listLinks;
    }



    //-------------------------------------------------------------------- виртуальные атрибуты PostMedia

    public $mediaType;
    public $mediaName;

    public $mediaFileName;
    public $mediaFile;




    //---------------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'post';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'target', 'type'], 'integer'],
            [['content'], 'string'],
            [['name'], 'string', 'max' => 250],
            //--------------------------------------------------------------------------- виртуальные атрибуты PostMedia
            [['mediaType', ], 'integer'],
            [['mediaName', 'mediaFileName'], 'string', 'max' => 250],
            [['mediaFile'], 'file'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'Владелец'),
            'target' => Yii::t('app', 'Цель'),
            'type' => Yii::t('app', 'Тип'),
            'name' => Yii::t('app', 'Название'),
            'ownerLastName' => Yii::t('app', 'Фамилия'),
            'ownerUsername' => Yii::t('app', 'Логин'),
            'content' => Yii::t('app', 'Содержимое'),
            'created_at' => Yii::t('app', 'Создано'),
            'updated_at' => Yii::t('app', 'Изменено'),
            //--------------------------------------------------------------------------- виртуальные атрибуты PostMedia

        ];
    }

    //************************************************************************************************ ДАННЫЕ СВЯЗАННЫХ ТАБЛИЦ

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserM()
    {
        return $this->hasOne(UserM::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostMedia()
    {
        return $this->hasMany(PostMedia::class, ['post_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(PostMedia::class, ['post_id' => 'id'])
            ->where(['type' => PostMedia::TYPE_IMAGE])
            ->orderBy('id');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLinks()
    {
        return $this->hasMany(PostMedia::class, ['post_id' => 'id'])
            ->where(['type' => PostMedia::TYPE_LINK])
            ->orderBy('id');
    }

    //************************************************************************************************ ГЕТТЕРЫ, СЕТТЕРЫ

    /**
     * @return mixed
     */
    public function getOwnerLastName()
    {
        $this->_ownerLastName = '';
        if (isset($this->userM) && isset($this->userM->nameFam)){
            $this->_ownerLastName = $this->userM->nameFam;
        }
        return $this->_ownerLastName;
    }

    /**
     * @return mixed
     */
    public function getOwnerUsername()
    {
        $this->_ownerUsername = '';
        if (isset($this->userM) && isset($this->userM->username)){
            $this->_ownerUsername = $this->userM->username;
        }
        return $this->_ownerUsername;
    }

    /**
     * @return mixed
     */
    public function getMainImage()
    {
        $this->_mainImage = '';
        $images = $this->images;
        if (!empty($images)){
            foreach ($images as $image){
                $this->_mainImage = $image->urlToFile;
                break;
            }
        }
        return $this->_mainImage;
    }


    //************************************************************************************************ ПЕРЕОПРЕДЕЛЕННЫЕ МЕТОДЫ

    public function beforeSave($insert) {
        if ($insert){
            $this->user_id = (isset(\Yii::$app->user->id)) ? \Yii::$app->user->id : 0;
        }
        return parent::beforeSave($insert);
    }

    public function savePost()
    {
        $ret = $this->save();
        return $ret;
    }

    public function deletePost()
    {
        foreach ($this->postMedia as $item){
            if (!$item->deleteFile()){
                return false;
            }
        }
        return $this->delete();
    }


    //************************************************************************************************ ДРУГИЕ МЕТОДЫ

    /**
     * @return mixed
     */
    public static function getListType()
    {
        $ret = [
            self::TYPE_FRONT => Yii::t('app', 'Главная страница'),
            self::TYPE_TARGET => Yii::t('app', 'Привязка к цели'),
        ];

        return $ret;
    }

    public static function getTypeStr($type)
    {
        $ret = '';
        switch ($type){
            case self::TYPE_FRONT:
                $ret = Yii::t('app', 'Главная страница');
                break;
            case self::TYPE_TARGET:
                $ret = Yii::t('app', 'Привязка к цели');
                break;
        }
        return $ret;
    }




}
