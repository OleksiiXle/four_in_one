<?php

namespace common\widgets\xgrid\models;

use Yii;
use console\controllers\backgroundTasks\models\TaskWorker;
use common\models\behaviors\Result;

class GridUploadWorker extends TaskWorker
{
    use Result;

    const PORTION_TO_LOG_SIZE = 2; // какими кусками писать в файл результата
    const PROGERSS_STEP = 2; // %
    const TOTAL_COUNT = 200; // %
    const SLEEP_SECONDS = 0;

    public function run()
    {
        try {
            // throw new \Exception('test exeption');
            $ret = $this->task->setTimeLimit(3600);
            $pathToFile = Yii::$app->basePath . '/runtime/uploads/';
            if (!is_dir($pathToFile)) {
                mkdir($pathToFile, 0777, true);
            }
            $fullFileName = $pathToFile . 'report_' . $this->task->user_id . '_' . time() . '.csv';
            $this->task->setResult($fullFileName);
            switch ($this->arguments['uploadFunction']) {
                case 'custom':
                    $filterModel = new $this->arguments['filterModel']();
                    foreach ($this->arguments['query'] as $item) {
                        if ($item['name'] == 'checkedIds') {
                            $filterModel->{$item['name']} = json_decode($item['value'], true);
                        } else {
                            $filterModel->{$item['name']} =  $item['value'];
                        }
                    }
                    $this->prepareFileCustom($filterModel, $fullFileName);
                    break;
                case 'default':
                    $consoleFilter = [];
                    foreach ($this->arguments['query'] as $item) {
                        if ($item['name'] == 'checkedIds') {
                            $consoleFilter[$item['name']] = json_decode($item['value'], true);
                        } else {
                            $consoleFilter[$item['name']] =  $item['value'];
                        }
                    }
                    $consoleFilter['actionWithChecked'] =  true;
                    $this->prepareFileDefault($this->arguments['gridModel'], $fullFileName, $consoleFilter);
                    break;
            }

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

    private function prepareFileDefault($gridModel, $fullFileName, $consoleFilter)
    {
        try {
           // throw new \Exception('test exeption');
            $this->resetResult();
            $this->task->setProgress(0);
            $this->task->setCustomStatus('Подготовка данных для выгрузки в файл ...');

            $grid = new $gridModel([
                'consoleFilter' => $consoleFilter,
            ]);
            $dataToUpload = $grid->upload();

            $this->logsInit(count($dataToUpload));

            $fp = fopen($fullFileName, 'w');
            $this->task->setCustomStatus('Выгрузка в файл ...');
            foreach ($dataToUpload as $data) {
                if ($this->done == 5) {
                //    throw new \Exception('test exeption');
                }
             //   $this->doLogs($this->done . '-' . $data->username);
                $this->doLogs();
                fputcsv($fp, $data);
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

    private function prepareFileCustom($filterModel, $fullFileName)
    {
        try {
           // throw new \Exception('test exeption');
            $this->resetResult();
            $this->task->setProgress(0);
            $this->task->setCustomStatus('Подготовка данных для выгрузки в файл ...');

            $filterModel->actionWithChecked = true;
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
