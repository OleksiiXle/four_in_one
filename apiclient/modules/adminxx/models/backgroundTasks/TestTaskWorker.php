<?php

namespace app\modules\adminxx\models\backgroundTasks;

use app\commands\backgroundTasks\models\TaskWorker;
use app\commands\backgroundTasks\models\BackgroundTask;

class UsersListUploadWorker extends TaskWorker
{
    const PORTION_TO_LOG_SIZE = 5; // какими кусками писать в файл результата
    const PROGERSS_STEP = 2; // %
    const TOTAL_COUNT = 200; // %
    const SLEEP_SECONDS = 1;

    public function run()
    {
        try {
            // throw new \Exception('test exeption');
            $ret = $this->task->setTimeLimit(60);
            $this->arguments['task'] = $this->task;
            $testTaskModel = new TestTaskModel($this->arguments);
            $testTaskModel->doTestBackgrounTask();
            if ($testTaskModel->resultSuccess) {
                if ($testTaskModel->resultOperationSuccess) {
                    return true;
                } else {
                    $this->errorMessage = '*error*Операция прошла неудачно. <br>' . $testTaskModel->resultAsStringHtml;
                    return false;
                }
            } else {
                $this->errorMessage = '*error*Системная ошибка. Сообщите Вашему администратору. <br>' . $testTaskModel->resultAsStringHtml;
                return false;
            }
        } catch (\Exception $e) {
            $this->errorMessage = str_replace(PHP_EOL, '<br>', $e->getMessage()
                . '<br>'
                . str_replace(PHP_EOL, '<br>', $e->getTraceAsString()));
            return false;
        }
    }

    public static function doTestBackgrounTask($id, BackgroundTask $task)
    {
        //throw new \Exception('test exeption');

        $result = [
            'status' => false,
            'data' => 'Error'
        ];
        try {
            // -- данные для логирования процесса
            $totalCount = self::TOTAL_COUNT;
            $portionToProgress = intval(self::PROGERSS_STEP * $totalCount / 100);
            $progressPercentCount = 0;
            $done = 0;
            $reportStr = '';

            $transaction = \Yii::$app->db->beginTransaction();
            for ($i = 1; $i < $totalCount; $i++) {
                /*
                    do something
                */
                //     sleep(2);

                $reportStr .= 'running job for id=' . $id . ' progres=' . $progressPercentCount . '% step ' . $i . ' ...' . PHP_EOL;
                $done++;
                if ($done > 120) {
                  //  throw new \Exception('test exeption on step 40');
                }

                if ($done % self::PORTION_TO_LOG_SIZE === 0) {
                    sleep(self::SLEEP_SECONDS);
                    $task->writeTemporaryResultToFile($reportStr);
                    $reportStr = '';
                }

                if ($portionToProgress > 0 && ($done % $portionToProgress === 0)) {
                    sleep(self::SLEEP_SECONDS);
                    $progressPercentCount += self::PROGERSS_STEP;
                    $task->setProgress($progressPercentCount);
                }
            }
            $transaction->commit();

            if ($progressPercentCount < 100) {
                $reportStr .= 'running job for id=' . $id . ' progres=' . 100 . '% step ' . $i . ' ...' . PHP_EOL;
            }

            if (!empty($reportStr)) {
                $task->writeTemporaryResultToFile($reportStr);
            }
            $task->setProgress(100);


            $result = [
                'status' => true,
                'data' => ''
            ];

        } catch (\Exception $e) {
            if (isset($transaction) && $transaction->isActive) {
                $transaction->rollBack();
            }
            $errors = 'Exception: ' . $e->getMessage() . PHP_EOL;
            $errors .= 'File: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;

            $result['data']= $errors;
        }

        return $result;
    }




}
