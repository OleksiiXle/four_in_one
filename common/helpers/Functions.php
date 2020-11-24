<?php

namespace common\helpers;

use yii\base\Exception;
use yii\helpers\FileHelper;

class Functions
{
    public static $rightArray=[
        0 => 'a_main_code',
        1 => '',
        2 => '',
        3 => '',
        4 => '',
        5 => '',
        6 => '',
        7 => '',
    ];
    public static $exelHeader = [
        1 => 'A',
        2 => 'B',
        3 => 'C',
        4 => 'D',
        5 => 'E',
        6 => 'F',
        7 => 'G',
        8 => 'H',
        9 => 'I',
        10 => 'J',
        11=> 'K',
        12=> 'L',
        13=> 'M',
        14=> 'N',
        15=> 'O',
        16=> 'P',
        17=> 'R',
        18=> 'Q',
        19=> 'S',
        20=> 'T',
        21=> 'Y',
        22=> 'W',
        23=> 'U',
        24=> 'V',
        25=> 'Z',
        26=> 'X',
    ];


    public static function intToDate($i){
        $res =  (isset($i) && is_numeric($i) && ($i>0)) ? date('d.m.Y',  $i) : '';
        return $res;
    }

    public static function dateToInt($d){
        if (isset($d) && is_string($d)){
            if ($d == ''){
                return null;
            }
            $arr = date_parse($d);
            $res = mktime(0, 0, 0,  $arr['month'],$arr['day'], $arr['year']);
            return $res;
        } else
            return null;
    }

    public static function dateTimeToInt($d){
        if (isset($d) && is_string($d)){
            if ($d == ''){
                return null;
            }
            $arr = date_parse($d);
            $res = mktime($arr['hour'], $arr['minute'], $arr['second'],  $arr['month'],$arr['day'], $arr['year']);
            return $res;
        } else
            return null;
    }

    public static function intToDateTime($i){
        $res =  (isset($i) && is_numeric($i)) ? date('d.m.Y H:i',  $i) : '';
        return $res;
    }

    public static function uploadFileXle($fileName, $unlink = true)
    {
        $result =[
            'status' => false,
            'data'   => 'Помилка вивантаження файлу',
            ];
        try{
            $options['mimeType'] = FileHelper::getMimeTypeByExtension($fileName);
            $attachmentName = basename($fileName);
            \Yii::$app->response->sendFile($fileName, $attachmentName, $options);
            $result['status'] = true;
            $result['data'] = 'Файл ' . $fileName . ' успішно вивантажено';
            if ($unlink){
                unlink($fileName);
            }
        } catch (Exception $e){
            $result['data'] = $e->getMessage();

        }
        return $result;
    }

    //*************************** CVS *****************************************************************
    /**
     * Вывод трех мерного ассоциативного массива в CSV файл
     * - ключи первого подмассива будут в превом ряду
     * @param array $data - массив
     * @param string $pathToFile
    * @param string $fileMask
     * @return mixed
     */
    public static function exportToCSV($data, $pathToFile, $fileMask = 'report'){
        try{
            $user = \Yii::$app->user->getId();
            $fileName = $pathToFile . '/' . $fileMask . '_' . $user . '.csv';
            $fp = fopen($fileName, 'w');

            $headerArr = array_keys($data[0]);
            fputcsv($fp, $headerArr);
            foreach ($data as $fields) {
                fputcsv($fp, $fields);
            }
            fclose($fp);
            return 'o.k.';
        } catch (Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * Чтение CSV файла в массив, возвращает массив
     * @param $fileName
     * @return array|string
     */
    public static function readCSV_ToArray($fileName){
        if (!file_exists($fileName) ) {
            return 'file not found ' . $fileName;
        }
        if (!is_readable($fileName)) {
            return 'file not is_readable ' . $fileName;
        }
        $data = [];
        if (($handle = fopen($fileName, 'r')) !== false) {
            $colName = fgetcsv($handle, 1000, ',');
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $buf = [];
                for ($i=0; $i < count($row); $i++){
                    $buf[$colName[$i]]=$row[$i];
                }
                if (!is_numeric($buf['i_positions_amount'])){
                    $buf['i_positions_amount'] = 0;
                }
                $data[]= $buf;
            }
            fclose($handle);
            return $data;
        } else {
            return 'file not fgetcsv ' . $fileName;
        }
    }

    public static function isFreeLock($lockName)
    {
        $isFreeLock = \Yii::$app->db->createCommand('SELECT IS_FREE_LOCK("'. $lockName .'")')->queryScalar();

        return ($isFreeLock == 1);
    }

    public static function getLock($lockName, $seconds = 7200)
    {
        $getLock = \Yii::$app->db->createCommand('SELECT GET_LOCK("'. $lockName .'", ' . $seconds . ')')->queryScalar();

        return ($getLock == 1);
    }

    public static function releaseLock($lockName)
    {
        $releaseLock = \Yii::$app->db->createCommand('SELECT RELEASE_LOCK("'. $lockName .'")')->queryScalar();

        return ($releaseLock == 1);
    }

    public static function dbg()
    {
        $rec['METHOD'] = \Yii::$app->request->getMethod();
        $rec['HEADERS'] = \Yii::$app->request->headers;
        //  $rec['RAW_BODY'] = \Yii::$app->request->rawBody;
        //  $rec['BODY_PARAMS'] = \Yii::$app->request->bodyParams;
        $rec['QUERY_PARAMS'] = \Yii::$app->request->queryParams;
        $rec['COOCIES'] = \Yii::$app->request->cookies;
        if (\Yii::$app->request->isPost){
            $rec['POST'] = \Yii::$app->request->post();
        }
        \yii::trace('************************************************ REQUEST', "dbg");
        \yii::trace(\yii\helpers\VarDumper::dumpAsString($rec), "dbg");

    }

    public static function log($data, $time=false)
    {
        $filename = \Yii::getAlias('@common'). '/runtime/logs/xleLog.log';
        if (is_array($data)) {
            ob_start();
            var_dump($data);
            $ret = ob_get_clean();
        } else {
            $ret = $data;
        }
        if ($time) {
            file_put_contents($filename,'******* ' . Functions::intToDateTime(time()) . PHP_EOL, FILE_APPEND);
        }
        file_put_contents($filename,$ret . PHP_EOL, FILE_APPEND);
    }

    public static function logRequest()
    {
        $filename = \Yii::getAlias('@common'). '/runtime/logs/xleLog.log';

        $request = \Yii::$app->request;
        $data['METHOD'] = $request->getMethod();
        $data['HEADERS'] = $request->headers;
        //  $rec['RAW_BODY'] = \Yii::$app->request->rawBody;
        //  $rec['BODY_PARAMS'] = \Yii::$app->request->bodyParams;
        $data['QUERY_PARAMS'] = $request->queryParams;
        $data['COOCIES'] = $request->cookies;
        if (\Yii::$app->request->isPost){
            $data['POST_DATA'] = $request->post();
        }
        file_put_contents($filename,'*** REQUEST *** ' . Functions::intToDateTime(time()) . PHP_EOL, FILE_APPEND);
        file_put_contents($filename,print_r($data, true) . PHP_EOL, FILE_APPEND);
    }

    public static function logClientRequest($request)
    {
        $filename = \Yii::getAlias('@common'). '/runtime/logs/xleLog.log';

        $data['METHOD'] = $request->getMethod();
        $data['HEADERS'] = $request->headers;
        //  $rec['RAW_BODY'] = \Yii::$app->request->rawBody;
        //  $rec['BODY_PARAMS'] = \Yii::$app->request->bodyParams;
        $data['QUERY_PARAMS'] = $request->queryParams;
        $data['COOCIES'] = $request->cookies;
        if (\Yii::$app->request->isPost){
            $data['POST_DATA'] = $request->post();
        }
        file_put_contents($filename,'*** REQUEST *** ' . Functions::intToDateTime(time()) . PHP_EOL, FILE_APPEND);
        file_put_contents($filename,print_r($data, true) . PHP_EOL, FILE_APPEND);
    }


}