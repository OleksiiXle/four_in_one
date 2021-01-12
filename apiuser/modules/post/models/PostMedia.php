<?php

namespace apiuser\modules\post\models;

use Yii;
use common\models\FileUpload;
use common\models\MainModel;

/**
 * This is the model class for table "post_media".
 *
 * @property int $id ID
 * @property int $post_id Post_id
 * @property int $type Тип
 * @property string $name Название
 * @property resource $file_name Имя файла
 * @property int $created_at Создано
 * @property int $updated_at Изменено
 *
 * @property Post $post
 */
class PostMedia extends MainModel
{

    const TYPE_IMAGE = 1;
    const TYPE_VIDEO = 2;
    const TYPE_AUDIO = 3;
    const TYPE_DOC = 4;
    const TYPE_LINK = 5;

    const TYPE_LIST = [
        self::TYPE_IMAGE => 'image',
        self::TYPE_VIDEO => 'video',
        self::TYPE_AUDIO => 'audio',
        self::TYPE_DOC => 'doc',
        self::TYPE_LINK => 'link',
    ];

    const SCENARIO_IMAGE  = 'image';
    const SCENARIO_VIDEO  = 'video';
    const SCENARIO_AUDIO  = 'audio';
    const SCENARIO_DOC  = 'doc';
    const SCENARIO_LINK  = 'link';


    private $_urlToFile;
    private $_pathToFile;

    public $mediaFile;
  //  public $imageFile;
 //   public $videoFile;
  //  public $audioFile;
  //  public $textFile;

    public function scenarios()
    {
        $ret = parent::scenarios();
        $ret[self::SCENARIO_IMAGE] = [
            'post_id', 'name', 'created_at', 'updated_at',
            'type', 'file_name'
        ];
        $ret[self::SCENARIO_VIDEO] = [
            'post_id', 'name', 'created_at', 'updated_at',
            'type', 'file_name'
        ];
        $ret[self::SCENARIO_AUDIO] = [
            'post_id', 'name', 'created_at', 'updated_at',
            'type', 'file_name'
        ];
        $ret[self::SCENARIO_DOC] = [
            'post_id', 'name', 'created_at', 'updated_at',
            'type', 'file_name'
        ];
        $ret[self::SCENARIO_LINK] = [
            'post_id', 'name', 'created_at', 'updated_at',
            'type', 'file_name'
        ];
        return $ret ;
    }


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'post_media';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['post_id', 'name'], 'required'],
            [['post_id', 'type'], 'integer'],
            [['name', 'file_name'], 'string', 'max' => 250],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::className(), 'targetAttribute' => ['post_id' => 'id']],

            ['file_name', 'url', 'defaultScheme' => 'http', 'on' => self::SCENARIO_LINK],

            //--------------------------------------------------------------------------- виртуальные атрибуты
         //   [['mediaFile',], 'file'],
        //    [['imageFile', 'videoFile'. 'audioFile' , 'textFile'], 'file'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'post_id' => Yii::t('app', 'Post_id'),
            'type' => Yii::t('app', 'Тип'),
            'name' => Yii::t('app', 'Название'),
            'file_name' => Yii::t('app', 'Имя файла'),
            'created_at' => Yii::t('app', 'Создано'),
            'updated_at' => Yii::t('app', 'Изменено'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPost()
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
    }

    /**
     * @return mixed
     */
    public function getListType()
    {
        $this->_listType = [
            self::TYPE_IMAGE => Yii::t('app', 'Изображение'),
            self::TYPE_VIDEO => Yii::t('app', 'Видео'),
            self::TYPE_AUDIO => Yii::t('app', 'Аудио'),
            self::TYPE_DOC => Yii::t('app', 'Документ'),
            self::TYPE_LINK => Yii::t('app', 'Ссылка'),
        ];

        return $this->_listType;
    }

    public function savePostMedia($userId)
    {
        $t=1;
        if (empty($this->type)){
            $this->addError('type', "Не указан тип");
            return false;
        }
        $this->scenario = self::TYPE_LIST[$this->type];
        if ($this->validate()){
            if ($this->type !=  self::TYPE_LINK){
                $ret = FileUpload::saveMediaFromTmp($userId, $this->file_name, self::TYPE_LIST[$this->type]);
                if ($ret['status']){
                    $this->file_name = str_replace('tmp_', '', $this->file_name);
                    return $this->save(false);
                } else {
                    $this->addError('file_name', $ret['data']);
                }
            } else {
                return $this->save(false);
            }
        } ///4731 2191 1733 4825
        return false;
    }

    /**
     * @return mixed
     */
    public function getUrlToFile()
    {
        $params = \Yii::$app->params;
        $alias = str_replace($params['appAlias'], $params['userHostName'], \Yii::getAlias('@web'));
        $this->_urlToFile = Yii::$app->request->hostInfo . $alias . $params['pathToFiles']
            . '/' . self::TYPE_LIST[$this->type] . '/' . $this->post->user_id . '/' . $this->file_name;
        return $this->_urlToFile;
    }

    /**
     * @return mixed
     */
    public function getPathToFile()
    {
        $this->_pathToFile = \Yii::getAlias('@app') . '/web' . \Yii::$app->params['pathToFiles'] . '/'
            . self::TYPE_LIST[$this->type] . '/' . $this->post->user_id . '/' . $this->file_name;
        return $this->_pathToFile;
    }


    public function deleteFile()
    {
        try{
            $ret = unlink($this->pathToFile);
            return $ret;
        } catch (\Exception $e){
            $this->addError('name', $e->getMessage());
        }
        return false;

    }

    public function deletePostMedia()
    {
        try{
            if ($this->type === self::TYPE_IMAGE
                || self::TYPE_AUDIO
                || self::TYPE_DOC
                || self::TYPE_VIDEO
            ){
                if ($this->deleteFile()){
                    return $this->delete();
                }
            } else {
                return $this->delete();
            }
        } catch (\Exception $e){
            $this->addError('name', $e->getMessage());
        }
        return false;
    }


}
