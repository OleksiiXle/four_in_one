<?php

namespace apiserver\modules\v1\controllers;

use app\helpers\Functions;
use yii\rest\Controller;

class SystemController extends Controller
{

    public function actionError() {
        $errorHandler = \Yii::$app->errorHandler;
        $exception = $errorHandler->exception;

        $errorResponse = [
            'name' => 'Internal Server Error',
            'message' => 'Please contact technical support',
        ];

        if ($exception) {
            /* @var $exception \yii\web\HttpException|\Exception */
            /* @var $handler \yii\web\ErrorHandler */
            if ($exception instanceof \yii\web\HttpException) {
                $code = $exception->statusCode;
            } else {
                $code = $exception->getCode();
            }
            $name = $handler->getExceptionName($exception);
            if ($name === null) {
                $name = 'Error';
            }
            if ($code) {
                $name .= " (#$code)";
            }
/*
            if ($exception instanceof \yii\base\UserException) {
                $message = $exception->getMessage();
            } else {
                $message = 'An internal server error occurred.';
            }
*/
            $message = $exception->getMessage();

            $errorResponse = [
                'name' => $name,
                'message' => $message,
            ];
            if ($code) {
                $errorResponse['code'] = $code;
            }
        }
        \common\helpers\Functions::log('**************** class SystemController');
        \common\helpers\Functions::log($errorResponse);
   //     \yii::trace('************************************************ $exception' , "dbg");
      //  \yii::trace(\yii\helpers\VarDumper::dumpAsString($exception), "dbg");
      //  \yii::trace(\yii\helpers\VarDumper::dumpAsString($errorResponse), "dbg");
    //    \yii::trace(\yii\helpers\VarDumper::dumpAsString($errorHandler), "dbg");

        return $errorResponse;
    }
}