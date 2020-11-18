<?php
namespace common\models;

use yii\base\Model;
use Yii;

class FileUpload extends Model
{
    public $fullFileName;
    public $webFullFileName;
    public $name;
    public $extension;
    public $tempName;
    public $type;
    public $size;
    public $error;
    public $waitingType;


    public function rules()
    {
        return [
            [['name'], 'validateFile'],
        //    [['mediaFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg'],
        ];
    }

    public function validateFile()
    {
        $params = \Yii::$app->params[$this->waitingType];
        // Массив с названиями ошибок
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE   => 'Размер файла превысил значение upload_max_filesize в конфигурации PHP.',
            UPLOAD_ERR_FORM_SIZE  => 'Размер загружаемого файла превысил значение MAX_FILE_SIZE в HTML-форме.',
            UPLOAD_ERR_PARTIAL    => 'Загружаемый файл был получен только частично.',
            UPLOAD_ERR_NO_FILE    => 'Файл не был загружен.',
            UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка.',
            UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск.',
            UPLOAD_ERR_EXTENSION  => 'PHP-расширение остановило загрузку файла.',
        ];
        if ($this->error !== UPLOAD_ERR_OK){
            $this->addError('name', $errorMessages[$this->error]);
            return false;
        }
        if (!is_uploaded_file($this->tempName)){
            $this->addError('name', 'Так делать не надо');
            return false;
        }
        switch ($this->waitingType){
            case 'image':
                $fi = finfo_open(FILEINFO_MIME_TYPE);
                $mime = (string) finfo_file($fi, $this->tempName);
                if (!in_array($this->extension, $params['availableExtensions'])
                    || strpos($mime, 'image') === false
                ){
                    $this->addError('name', Yii::t('app',
                        'Допустимые типы ' . implode(',',$params['availableExtensions'] ) ));
                }
                if ($this->size > (integer) $params['maxSize'] || filesize($this->tempName) > $params['maxSize']){
                    $this->addError('name', Yii::t('app', 'Максимальный размер ' . $params['maxSize']));
                }
                break;
            default:
                $this->addError('name', Yii::t('app', 'Неверный тип медиаресурса ' . $this->waitingType));
                break;
        }
        return $this->hasErrors();


    }

    public function getInstance($type)
    {
        $r=1;
        if (!empty($_FILES)){
            $this->waitingType = $type;
            $this->name = $_FILES[0]['name'];
            $this->tempName = $_FILES[0]['tmp_name'];
            $this->type = $_FILES[0]['type'];
            $this->size = $_FILES[0]['size'];
            $this->extension = strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
            $this->error = $_FILES[0]['error'];
            return $this->validate();

        } else {
            $this->addError('name', 'no files');
        }
        return false;
    }

    public function uploadToTmp($userId)
    {
        $pathTofiles = \Yii::getAlias('@app') . '/web' . \Yii::$app->params['pathToFiles'] . '/tmp';

        $fileMask = 'tmp_' . $this->waitingType . '_' . $userId;
        $i = is_dir($pathTofiles);
        try {
            if (!is_dir($pathTofiles)){
                $this->addError('name', $pathTofiles . '  не найден');
                return false;
            } else {
                $pattern = "$pathTofiles/$fileMask*";
                array_map("unlink", glob($pattern));
                $tmpName = $fileMask . '_' . time() . '.' . $this->extension;
                $this->fullFileName = $pathTofiles . '/' .  $tmpName;
                $ret = move_uploaded_file($this->tempName, $this->fullFileName);
                if ($ret) {
                    $this->webFullFileName = Yii::getAlias('@web/files/tmp/' . $tmpName);
                    $this->name = $tmpName;
                    return true;
                }
            }
        } catch (\Exception $e){
            $this->addError('name', $e->getMessage());
        }
        return false;
    }

    public static function saveMediaFromTmp($userId, $fileName, $type = 'image')
    {
        $result = [
            'status' => false,
            'data' => 'error'
        ];
        try {
            $newPathTofiles = \Yii::getAlias('@app') . '/web' . \Yii::$app->params['pathToFiles'] . '/' . $type . '/' . $userId;
            if (!is_dir($newPathTofiles)){
                $ret = mkdir($newPathTofiles, 0777);
            }

            $oldFilename = \Yii::getAlias('@app') . '/web' . \Yii::$app->params['pathToFiles'] . '/tmp/' . $fileName;
            $newFileName = $newPathTofiles . '/' . str_replace('tmp_', '', $fileName);

            $ret = rename($oldFilename, $newFileName);
      //      $ret = unlink($oldFilename);
            $result = [
                'status' => true,
                'data' => 'ok'
            ];
        } catch (\Exception $e){
            $result['data'] = $e->getMessage();

        }
        return $result;

    }


}
?>