<?php

namespace common\widgets\xlegrid\models;

use Yii;
use console\backgroundTasks\models\TaskWorker;
use common\models\behaviors\Result;

class GridUploadWorker extends TaskWorker
{
    use Result;

    const PORTION_TO_LOG_SIZE = 2; // какими кусками писать в файл результата
    const PROGERSS_STEP = 2; // %
    const TOTAL_COUNT = 200; // %
    const SLEEP_SECONDS = 2;

    public function run()
    {
        try {
            // throw new \Exception('test exeption');
            $ret = $this->task->setTimeLimit(3600);
            $arguments = $this->arguments;
            $filterModel = new $this->arguments['filterModel']();
          //  $filterModel->setAttributes($this->arguments['attributes']);
            foreach ($this->arguments['query'] as $item) {
                $filterModel->{$item['name']} =  $item['value'];
            }
            /*
            if (!empty($this->arguments['checkedIds'])) {
                $filterModel->checkedIds = $this->arguments['checkedIds'];
            }
            */

            $pathToFile = Yii::$app->basePath . '/runtime/uploads/';
            if (!is_dir($pathToFile)) {
                mkdir($pathToFile, 0777, true);
            }
            $fullFileName = $pathToFile . 'report_' . $this->task->user_id . '_' . time() . '.csv';
            $this->task->setResult($fullFileName);
            $this->prepareFile($filterModel, $fullFileName);
            if ($this->resultSuccess) {
                if ($this->resultOperationSuccess) {
                    return true;
                } else {
                    $this->errorMessage = '*error*Операция прошла неудачно. <br>' . $this->getResultAsStringHtml();
                    return false;
                }
            } else {
                $this->errorMessage = '*error*Системная ошибка. Сообщите Вашему администратору. <br>'
                    . $this->getResultAsStringHtml();
                return false;
            }
        } catch (\Exception $e) {
            $this->errorMessage = str_replace(PHP_EOL, '<br>', $e->getMessage()
                . '<br>'
                . str_replace(PHP_EOL, '<br>', $e->getTraceAsString()));
            return false;
        }
    }

    private function prepareFile($filterModel, $fullFileName)
    {
        try {
           // throw new \Exception('test exeption');
            $this->resetResult();
            $this->task->setProgress(0);
            $this->task->setCustomStatus('Подготовка данных для выгрузки в файл ...');

            $dataToUpload = $filterModel->getQuery()->all();
            $this->logsInit(count($dataToUpload));

            $fp = fopen($fullFileName, 'w');

            $arrayRow = [];
            foreach ($filterModel->getDataForUpload() as $attribute => $description) {
                $arrayRow[] = $description['label'];
            }
            fputcsv($fp, $arrayRow);

            $this->task->setCustomStatus('Выгрузка в файл ...');
            foreach ($dataToUpload as $data) {
                if ($this->done == 5) {
                //    throw new \Exception('test exeption');
                }
             //   $this->doLogs($this->done . '-' . $data->username);
                $this->doLogs();
                $arrayRow = [];
                foreach ($filterModel->getDataForUpload() as $attribute => $description) {
                    if ($description['content'] == 'value') {
                        $arrayRow[] = $data->{$attribute};
                    } elseif($description['content'] instanceof \Closure) {
                        $arrayRow[] = call_user_func($description['content'], $data);
                    } else {
                        $arrayRow[] = 'no data';
                    }
                }
                fputcsv($fp, $arrayRow);
            }
            $this->resultSuccess = true;
            $this->resultOperationSuccess = true;
            $this->task->setProgress(100);
            $this->task->setCustomStatus('Операция успешно завершена');
        } catch (\Exception $e) {
            $this->setResultSuccess($this->reportPortion);
            $this->resetResult();
            $errorsArray = $this->prepareErrorStringToHtml($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $this->setResultError($errorsArray, false);
            return false;
        }

        return true;
    }
}
