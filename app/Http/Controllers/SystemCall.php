<?php
/**
 * Created by PhpStorm.
 * User: gokuai
 * Date: 17/8/31
 * Time: 17:47
 */

namespace App\Http\Controllers;


class SystemCall
{
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke(Task $task, Scheduler $scheduler)
    {
        $callback = $this->callback;
        return $callback($task, $scheduler);
    }
}