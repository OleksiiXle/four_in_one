<?php

namespace common\helpers;

use Yii;

class FileHelper
{
    const FILE_TYPES_VALID = [
        'application/pdf',
        //text
        'text/plain',
        'text/csv',
        'text/x-csv',
        'inode/x-empty',
        'text/html',
        'text/css',
        //images
        'image/png',
        'image/gif',
        'image/jpeg',
        // archives
        'application/x-compress',
        'application/zip',
        'application/x-zip-compressed',
        'application/x-rar',
        'application/x-rar-compressed',
        'application/vnd.rar',
        'application/x-tar',
        'application/x-7z-compressed',
        'application/x-lzma',
        //word documents
        'application/msword',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.template', 'application/vnd.ms-word.document.macroEnabled.12', 'application/vnd.ms-word.template.macroEnabled.12',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.template', 'application/vnd.ms-excel.sheet.macroEnabled.12', 'application/vnd.ms-excel.template.macroEnabled.12',
        'application/vnd.ms-excel.addin.macroEnabled.12', 'application/vnd.ms-excel.sheet.binary.macroEnabled.12', 'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'application/vnd.openxmlformats-officedocument.presentationml.slideshow', 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
        'application/vnd.ms-powerpoint.presentation.macroEnabled.12', 'application/vnd.ms-powerpoint.template.macroEnabled.12', 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
        'application/vnd.ms-access', 'application/vnd.ms-office', 'application/xml', 'text/xml',
        //openoffice documents
        'application/vnd.oasis.opendocument.text', 'application/vnd.oasis.opendocument.text-template', 'application/vnd.oasis.opendocument.text-web',
        'application/vnd.oasis.opendocument.text-master', 'application/vnd.oasis.opendocument.graphics', 'application/vnd.oasis.opendocument.graphics-template',
        'application/vnd.oasis.opendocument.presentation', 'application/vnd.oasis.opendocument.presentation-template', 'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.oasis.opendocument.spreadsheet-template', 'application/vnd.oasis.opendocument.chart', 'application/vnd.oasis.opendocument.formula',
        'application/vnd.oasis.opendocument.database', 'application/vnd.oasis.opendocument.image', 'application/vnd.openofficeorg.extension',
        'application/octet-stream',
        //audio
        'audio/ac3', 'audio/basic', 'audio/midi', 'audio/mpeg', 'audio/prs.sid', 'audio/vnd.rn-realaudio', 'audio/x-aac', 'audio/x-adpcm',
        'audio/x-aifc', 'audio/x-aiff', 'audio/x-aiff', 'audio/x-aiffc', 'audio/x-flac', 'audio/x-m4a', 'audio/x-mod',
        'audio/x-mp3-playlist', 'audio/x-mpeg', 'audio/x-mpegurl', 'audio/x-ms-asx', 'audio/x-pn-realaudio',
        'audio/x-pn-realaudio', 'audio/x-riff', 'audio/x-s3m', 'audio/x-scpls', 'audio/x-scpls', 'audio/x-stm',
        'audio/x-voc', 'audio/x-wav', 'audio/x-xi', 'audio/x-xmxm', 'audio/mpeg3', 'audio/x-mpeg-3',
        //video
        'video/3gpp', 'video/dv', 'video/isivideo', 'video/mpeg', 'video/quicktime', 'video/vivo', 'video/vnd.rn-realvideo', 'video/wavelet',
        'video/x-3gpp2', 'video/x-anim', 'video/x-avi', 'video/x-flic', 'video/x-mng', 'video/x-ms-asf', 'video/x-ms-wmv',
        'video/x-msvideo', 'video/x-nsv', 'video/x-real-video', 'video/x-sgi-movie',
        'video/webm', 'video/mp4', 'video/mpeg', 'video/x-mpeg', 'video/x-matroska'
    ];

    public static function readDir($dir, $filter = '*.json', $flag = null)
    {
        return glob(Yii::$app->getBasePath() . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $filter, $flag);
    }

    public static function recursiveReadDir($dir, $filter = "*.json")
    {
        $files = FileHelper::readDir($dir, $filter);
        foreach (glob(Yii::$app->getBasePath() . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . "*", GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $dir = str_replace(Yii::$app->getBasePath() . DIRECTORY_SEPARATOR, "", $dir);

            $files = array_merge($files, FileHelper::recursiveReadDir($dir, $filter));
        }
        return $files;
    }

    public static function recursiveCopy($source, $dest)
    {
        if (!file_exists($source)) {
            return false;
        }
        if (is_dir($source)) {
            if (!is_dir($dest)) {
                mkdir($dest);
            }

            $dir_handle = opendir($source);
            while ($file = readdir($dir_handle)) {
                if ($file != "." && $file != "..") {
                    if (is_dir($source . "/" . $file)) {
                        if (!file_exists($dest . "/" . $file)) {
                            mkdir($dest . "/" . $file);
                        }
                        FileHelper::recursiveCopy($source . "/" . $file, $dest . "/" . $file);
                    } else {
                        if (!file_exists($dest . "/" . $file)) {
                            copy($source . "/" . $file, $dest . "/" . $file);
                        }
                    }
                }
            }
            closedir($dir_handle);
        } else {
            if (!file_exists($dest)) {
                copy($source, $dest);
            }
        }
    }

    /**
     * Recursive delete content of folder
     * @param $path - path to delete
     * @param bool $deleteFolder - if delete folder
     * @return bool
     */
    public static function recursiveRmDir($path, $deleteFolder = true)
    {
        // Protection from fools
        if ($path == '/') {
            return false;
        }

        if (!file_exists($path)) {
            return false;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileInfo) {
            $todo = ($fileInfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileInfo->getRealPath());
        }

        if ($deleteFolder == true) {
            rmdir($path);
            return true;
        } else {
            return true;
        }
    }

    public static function recursiveChmod($path, $filemode, $dirmode)
    {
        // Protection from fools
        if ($path == '/') {
            return false;
        }

        if (!file_exists($path)) {
            return false;
        }

        if (is_dir($path)) {
            if (!chmod($path, $dirmode)) {
                print $path;
                return false;
            }
            $dh = opendir($path);
            while (($file = readdir($dh)) !== false) {
                if ($file != '.' && $file != '..') {
                    $fullpath = $path . '/' . $file;
                    FileHelper::recursiveChmod($fullpath, $filemode, $dirmode);
                }
            }
            closedir($dh);
        } else {
            if (is_link($path)) {
                return false;
            }
            if (!chmod($path, $filemode)) {
                return false;
            }
        }
    }

    public static function generateFilename($path)
    {
        $error = 0;
        while (true) {
            $filename = Yii::$app->getSecurity()->generateRandomString(16);
            if (file_exists($path . DIRECTORY_SEPARATOR . $filename) == false) {
                return $filename;
            } else {
                $error++;
            }

            if ($error >= 10) {
                throw new InvalidParam('Cannot generate filename!');
            }
        }
        return null;
    }

    public static function sanitize($filename)
    {
        return str_replace([' ', '"', '\'', '&', '/', '\\', '?', '#'], '-', $filename);
    }

    /**
     * @param $filename - path to file
     * @return string
     */
    public static function getMimeType($filename)
    {
        $info = new \finfo(FILEINFO_MIME_TYPE);
        $type = $info->file($filename);
        return $type;
    }

    public static function formatFileSize($bytes, $roundLength = 2, $kb_is = 1024, $bits = false)
    {
        if ($bits == true) {
            $types = ['b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb'];
        } else {
            $types = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        }
        for ($i = 0; $bytes >= $kb_is && $i < (count($types) - 1); $i++) {
            $bytes /= $kb_is;
        }

        return (round($bytes, $roundLength) . " " . $types[$i]);
    }

    public static function tail($file, $lines = 50)
    {
        return `tail -n $lines $file`;
    }

    public static function getSudo()
    {
        if (file_exists("/usr/bin/sudo")) {
            $sudo = "/usr/bin/sudo";
        }

        if (file_exists("/usr/local/bin/sudo")) {
            $sudo = "/usr/local/bin/sudo";
        }

        if (file_exists("/usr/sbin/sudo")) {
            $sudo = "/usr/sbin/bin/sudo";
        }
        return $sudo;
    }

    public static function createTempDir($prefix = null)
    {
        $name = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid($prefix, true);

        if (file_exists($name)) {
            unlink($name);
        } elseif (is_dir($name)) {
            static::recursiveRmDir($name);
        }

        mkdir($name, 0777, true);

        if (is_dir($name)) {
            return $name;
        } else {
            return false;
        }
    }

    public static function findFiles($dir, $recursive = false)
    {
        if (!is_dir($dir)) {
            throw new InvalidParam('It is not dir!');
        }

        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        $list = [];
        $handle = opendir($dir);
        if ($handle === false) {
            throw new InvalidParam('Unable to open dir!');
        }

        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_file($path)) {
                $list[] = $path;
            } elseif ($recursive) {
                $list = array_merge($list, static::findFiles($path, $recursive));
            }
        }
        closedir($handle);

        return $list;
    }

    public static function getPathInfo($filename)
    {
        return pathinfo($filename);
    }

    public static function addIteratorForFilename($filePath, $fullPath = false)
    {
        $fileInfo = static::getPathInfo($filePath);

        $path = $fileInfo['dirname'];
        $filename = $fileInfo['filename'];
        $extension = $fileInfo['extension'];

        $fullFileName = $path . DIRECTORY_SEPARATOR . $filename . '.' . $extension;

        if (!file_exists($fullFileName)) {
            if ($fullPath) {
                return $fullFileName;
            } else {
                return $filename . '.' . $extension;
            }
        }

        $i = 0;
        while (file_exists($fullFileName)) {
            $i++;
            $fullFileName = $path . DIRECTORY_SEPARATOR . $filename . '_' . $i . '.' . $extension;
        }

        if ($fullPath) {
            return $fullFileName;
        } else {
            return $filename . '_' . $i . '.' . $extension;
        }
    }

    public static function getExtensionFromFilename($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    public static function fileLineCount($filename)
    {
        $cmd = "wc -l $filename | awk '{print $1}'";
        $result = exec($cmd);
        if (isset($result)) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Get count number of non-empty lines in a file
     * @param string $filePath Path to file
     * @return null|int Return null if error.
     */
    public static function getCountNonEmptyLinesInFile($filePath)
    {
        if (!file_exists($filePath) or !is_file($filePath)) {
            return null;
        }

        $cmd = "grep -cve '^\s*$' $filePath";
        $result = exec($cmd);

        return (int)trim($result);
    }

    public static function sanitizeFilename($filename)
    {
        return preg_replace('/[^a-zA-Z0-9\-\._]/', '_', $filename);
    }

    public static function normalizePath($path)
    {
        $path = rtrim(strtr($path, '/\\', DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);

        $parts = [];
        foreach (explode(DIRECTORY_SEPARATOR, $path) as $part) {
            if ($part === '..' and !empty($parts) and end($parts) !== '..') {
                array_pop($parts);
            } elseif ($part === '.' or $part === '' and !empty($parts)) {
                continue;
            } else {
                $parts[] = $part;
            }
        }

        $path = implode(DIRECTORY_SEPARATOR, $parts);

        return $path;
    }

    public static function writeStringToFile($path, $string)
    {
        file_put_contents($path, $string . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * @param string $filename - path to file
     * @param array $validFileTypes example ['application/pdf', 'image/gif']
     * @return bool
     */
    public static function validateFileType($filename, $validFileTypes = self::FILE_TYPES_VALID)
    {
        if (in_array(self::getMimeType($filename), $validFileTypes, true)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $path
     * @return int
     */
    public static function folderSize($path)
    {
        $size = 0;
        foreach (glob(rtrim($path, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : self::folderSize($each);
        }
        return $size;
    }

    /**
     * @param string $logFileName
     * @return string
     */
    public static function getWritableLogFile($logFileName)
    {
        if (!is_writable(dirname($logFileName)) || (file_exists($logFileName) && !is_writable($logFileName))) {
            return '/dev/null';
        }

        return $logFileName;
    }

    /**
     * Remove temporary files
     * @param $pathToTmpFiles
     * @param $checkTime
     * @return array
     */
    public static function killOldTmpFiles($pathToTmpFiles, $checkTime)
    {
        $result = [
            'status' => false,
            'data' => '',
        ];
        try {
            if (file_exists($pathToTmpFiles)) {
                $dir = opendir($pathToTmpFiles);
                $list = array();
                while($file = readdir($dir)){
                    if ($file != '.' and $file != '..'){
                        $list[$pathToTmpFiles . $file] = filectime($pathToTmpFiles . $file);
                    }
                }
                closedir($dir);
                krsort($list);

                $hasErrors = false;
                foreach ($list as $fileName => $fileTime) {
                    if ($fileTime < $checkTime) {
                        if (!unlink($fileName)) {
                            $hasErrors = true;
                            $result['data'] .= "File $fileName unlink failed" . PHP_EOL;
                        }
                    } else {
                        if (!$hasErrors) {
                            $result['status'] = true;
                        }
                        break;
                    }
                }

            } else {
                $result['data'] .= "Folder $pathToTmpFiles was not found";
            }
        } catch (\Exception $e) {
            $result['data'] = $e->getMessage();
        }

        return $result;
    }

}
