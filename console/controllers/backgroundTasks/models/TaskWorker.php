<?php

namespace console\backgroundTasks\models;

class TaskWorker
{
    protected $arguments;
    /** @var BackgroundTask */
    protected $task;
    public $result;
    public $errorMessage = '';

    const METHOD_RUN = 'run';
    const METHOD_ON_SUCCESS = 'onSuccess';

    public function setArguments($arguments)
    {
        $this->arguments = (!is_array($arguments))
            ? json_decode($arguments, true)
            : $arguments;
    }

    public function setTask($task)
    {
        $this->task = $task;
    }

    /**
     * Main method of background task.
     * @return bool
     */
    public function run()
    {
        return false;
    }

    /**
     * Callback which called when task successfully finished
     */
    public function onSuccess()
    {
    }

    /**
     * Callback which called when task finished with errors
     */
    public function onError()
    {
    }

    /**
     * Callback which called when task updating
     * @param int $progress
     */
    public function onProgress($progress)
    {
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getErrors($errors)
    {
        $this->task->result = $errors;
    }

}
