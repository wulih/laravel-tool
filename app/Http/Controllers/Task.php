<?php
/**
 * Created by PhpStorm.
 * User: gokuai
 * Date: 17/8/31
 * Time: 16:09
 */

namespace App\Http\Controllers;

class Task
{
    protected $taskId;
    protected $coroutine;
    protected $sendValue = null;
    protected $beforeFirstYield = true;
    
    public function __construct($taskId, \Generator $coroutine)
    {
        $this->taskId  = $taskId;
        $this->coroutine = $coroutine;
    }
    
    public function setSendValue($sendValue)
    {
        $this->sendValue = $sendValue;
    }

    public function getTaskId() {
        return $this->taskId;
    }
    
    public function run()
    {
        if ($this->beforeFirstYield) {
            $this->beforeFirstYield = false;
            return $this->coroutine->current();
        } else {
            $retVal = $this->coroutine->send($this->sendValue);
            $this->sendValue = null;
            return $retVal;
        }
    }
    
    public function isFinished()
    {
        return !$this->coroutine->valid();
    }
}