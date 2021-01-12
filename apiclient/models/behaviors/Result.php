<?php

namespace app\models\behaviors;

use app\commands\backgroundTasks\models\BackgroundTask;

/**
 * Trait Result
 * Можно добавлять классам, которые осуществляют операции и возвращают результат
 * @package app\models\behaviors
 */
trait Result
{
    /**
     * Результат работы метода класса, устанавливать в false - если метод слетел (был ексепшен)
     * @var bool
     */
    public $resultSuccess = false;
    /**
     * Результат выполнения методом класса операции, устанавливать true - если все получилось, false - если операция не была произведена
     * @var bool
     */
    public $resultOperationSuccess = false;
    /**
     * Класс фоновой задачи, если задан, в файл лога будет писаться промежуточный результат
     * @var null
     */
    // public $resultTask = null;
    /**
     * Массив результатов операции - простой ( 0 -> Какой-то текст1, 1 -> Какой-то текст2)
     * @var array
     */
    public $resultData = [];
    /**
     * Массив результатов операции - простой ( 0 -> Какой-то текст1, 1 -> Какой-то текст2)
     * @var array
     */
    public $resultErrorsData = [];

    /**
     * Геттер - возвращает $resultData
     * @var array
     */
    private $_resultAsArray = [];
    /**
     * Геттер - возвращает первую строку $resultData
     * @var string
     */
    private $_resultAsStringSimple = '';
    /**
     * Геттер - возвращает $resultData в виде текста с переводами строки
     * @var string
     */
    private $_resultAsStringTxt = '';
    /**
     * Геттер - возвращает $resultData в виде текста с <br>
     * @var string
     */
    private $_resultAsStringHtml = '';

    // -- данные для логирования процесса
    private $totalCount = 0;
    private $portionToProgress = 0;
    private $progressPercentCount = 0;
    private $done = 0;
    private $reportPortion = [];

    /**
     * @return array
     */
    public function getResultAsArray()
    {
        $this->_resultAsArray = $this->resultData;
        return $this->_resultAsArray;
    }

    /**
     * @return string
     */
    public function getResultAsStringSimple()
    {
        $this->_resultAsStringSimple = (empty($this->resultData)) ? '' : $this->resultData[0];

        return $this->_resultAsStringSimple;
    }

    /**
     * @return string
     */
    public function getResultAsStringTxt()
    {
        $this->_resultAsStringTxt = '';
        foreach ($this->resultData as $row) {
            $this->_resultAsStringTxt .= $row . PHP_EOL;
        }
        return $this->_resultAsStringTxt;
    }

    /**
     * @return string
     */
    public function getResultAsStringHtml()
    {
        $this->_resultAsStringHtml = '';
        foreach ($this->resultData as $row) {
            $this->_resultAsStringHtml .= str_replace(PHP_EOL, '<br>', $row) . '<br>';
        }

        return $this->_resultAsStringHtml;
    }

    /**
     * Сброс и инициализация результата
     *
     */
    public function resetResult()
    {
        $this->resultSuccess = true;
        $this->resultOperationSuccess = true;
        $this->resultData = [];
    }

    /**
     * Запись в $resultData данных, если есть $resultTask - в файл лога будет писаться промежуточный результат
     * @param array/string $data - простой массив или строка
     */
    public function setResultData($data, $error = false)
    {
        $errorPrefix = ($error) ? '*error*' : '';
        if (!empty($data)) {
            if (is_array($data)) {
                foreach ($data as $row) {
                    $this->resultData[] = $errorPrefix . $row;
                    $this->backgroundTaskLog($errorPrefix . $row);
                }
            } else {
                $this->resultData[] = $errorPrefix . $data;
                $this->backgroundTaskLog($errorPrefix . $data);
            }
        }
    }

    /**
     * Добавление в результат данных об ошибке
     * @param string/array $errorDescription
     * @param bool $success
     */
    public function setResultError($errorDescription, $success = false)
    {
        $this->resultSuccess = $success;
        $this->resultOperationSuccess = false;
        $this->setResultData($errorDescription, true);
    }

    /**
     * Добавление в результат данных об успехе
     * @param string/array $succesDescription
     */
    public function setResultSuccess($succesDescription)
    {
        $this->resultSuccess = true;
        $this->resultOperationSuccess = true;
        $this->setResultData($succesDescription, false);
    }

    /**
     * Вывод в файл лога промежуточный результат
     * @param $logTxt
     */
    private function backgroundTaskLog($logTxt)
    {
        if (!empty($this->task) && $this->task instanceof BackgroundTask) {
            $this->task->writeTemporaryResultToFile($logTxt . PHP_EOL);
        }
    }

    private function prepareErrorStringToHtml($str)
    {
        $ret = explode(PHP_EOL, $str);
        return $ret;
    }


    //****************************************************************************************** логирование процесса

    private function logsInit($totalCount)
    {
        if (!empty($totalCount)) {
            $this->totalCount = $totalCount;
            $this->portionToProgress = intval(self::PROGERSS_STEP * $totalCount / 100);
            $this->progressPercentCount = 0;
            $this->done = 0;
            $this->reportPortion = [];
        }
    }

    private function doLogs($resStr = '')
    {
        $this->done++;
        if (!empty($resStr)) {
            $this->reportPortion[] = $resStr;
            if (($this->done % self::PORTION_TO_LOG_SIZE === 0)) {
                $this->setResultSuccess($this->reportPortion);
                $this->reportPortion = [];
            }
        }

        if ($this->portionToProgress > 0 && ($this->done % $this->portionToProgress === 0)) {
            $this->progressPercentCount += self::PROGERSS_STEP;
            $this->task->setProgress($this->progressPercentCount);
        }
        self::doSleep();
    }

    private function addTologsPortion($data)
    {
        if (is_array($data)) {
            foreach ($data as $row) {
                $this->reportPortion[] = $row;
            }
        } else {
            $this->reportPortion[] = $data;
        }
    }

    private function doSleep()
    {
        if (self::SLEEP_SECONDS > 0) {
            sleep(self::SLEEP_SECONDS);
        }
    }
}