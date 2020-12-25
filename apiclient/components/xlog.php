<?php
namespace apiserver\models;

use common\helpers\Functions;

class xlog
{
    public static function log($data, $time=false)
    {
        $filename = \Yii::$app->basePath . '/runtime/logs/xleLog.log';

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
        $filename = \Yii::$app->basePath . '/runtime/logs/xleLog.log';

        $request = \Yii::$app->request;
        $rec['METHOD'] = $request->getMethod();
        $rec['HEADERS'] = $request->headers;
        //  $rec['RAW_BODY'] = \Yii::$app->request->rawBody;
        //  $rec['BODY_PARAMS'] = \Yii::$app->request->bodyParams;
        $rec['QUERY_PARAMS'] = $request->queryParams;
        $rec['COOCIES'] = $request->cookies;
        if (\Yii::$app->request->isPost){
            $rec['POST_DATA'] = $request->post();
        }

    }


}