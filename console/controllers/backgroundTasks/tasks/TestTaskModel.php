<?php

namespace console\backgroundTasks\tasks;

use yii\base\BaseObject;

class TestTaskModel extends BaseObject
{
    use \common\models\behaviors\Result;

    const PORTION_TO_LOG_SIZE = 6; // какими кусками писать в файл результата
    const PROGERSS_STEP = 2; // %
    const TOTAL_COUNT = 50; // %
    const SLEEP_SECONDS = 1;

    public $id;
    public $model;
    public $task;

    // -- данные для логирования процесса
    private $portionToProgress;
    private $progressPercentCount;
    private $done;
    public $reportPortion = [];

    public function init()
    {
        $this->resultTask = $this->task;
    }

    private function doSleep()
    {
        if (self::SLEEP_SECONDS > 0) {
            sleep(self::SLEEP_SECONDS);
        }
    }

    private function logsInit($totalCount)
    {
        if (!empty($totalCount)) {
            $this->portionToProgress = intval(self::PROGERSS_STEP * $totalCount / 100);
            $this->progressPercentCount = 0;
            $this->done = 0;
            $this->reportPortion = [];
        }
    }

    private function doLogs($reportStr)
    {
        $this->done++;
        $this->reportPortion[] = $reportStr;
        if (($this->done % self::PORTION_TO_LOG_SIZE === 0)) {
            $this->setResultSuccess($this->reportPortion);
            $this->reportPortion = [];
        }

        if ($this->portionToProgress > 0 && ($this->done % $this->portionToProgress === 0)) {
            $this->progressPercentCount += self::PROGERSS_STEP;
            $this->task->setProgress($this->progressPercentCount);
        }
        self::doSleep();
    }

    public function doTestBackgrounTask()
    {
        //throw new \Exception('test exeption');

        $this->task->setCustomStatus('Начинаю тестовую задачу ...');
        $this->logsInit(self::TOTAL_COUNT);
        $this->resetResult();
        try {

            $transaction = \Yii::$app->db->beginTransaction();
            for ($i = 1; $i <= self::TOTAL_COUNT; $i++) {
                /*
                    do something
                */

                $reportStr = 'running job for id=' . $this->id . ' progres=' . $this->progressPercentCount . '% step ' . $i . ' ...';
                $this->doLogs($reportStr);

                //---- примеры обработки ошибок:
                if ($i > 8) {
                    if (1 == 0) {
                        throw new \Exception('test exeption on step ' . ($i + 1));
                    }


                    //-- обработка некритической ошибки
                    if (1 == 1) {
                        //-- запись в лог незаписанной порции
                        $this->setResultSuccess($this->reportPortion);
                        $this->resetResult();

                        $this->task->setCustomStatus('Обработка некритической ошибки ...');
                        $this->setResultError('Failed running job for id=' . $this->id . ' progres='
                            . $this->progressPercentCount . '% step ' . ($i + 1) . ' ...', true);
                        $errorsMsgArray = [
                            'Error massage 1',
                            'Error massage 2',
                            'Error massage 3',
                            'Error massage 4',
                        ];
                        $this->setResultError($errorsMsgArray, true);

                        $transaction->rollBack();
                        return false;
                    }
                }

            }
            $transaction->commit();
            $this->task->setCustomStatus('Задача завершена ...');

            $this->setResultSuccess($this->reportPortion);
            $this->task->setProgress(100);

        } catch (\Exception $e) {
            if (isset($transaction) && $transaction->isActive) {
                $transaction->rollBack();
            }
            $this->setResultSuccess($this->reportPortion);
            $this->resetResult();
            $this->setResultError($e->getMessage() . PHP_EOL . $e->getTraceAsString(), false);
        }
        return true;
    }




}
