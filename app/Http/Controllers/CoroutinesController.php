<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CoroutinesController extends Controller
{
    public function index()
    {
       // $scheduler = new Scheduler;
        //$scheduler->newTask($this->server(8000));
       // $scheduler->run();
        $gen = $this->gen();
        var_dump($gen->current());
        var_dump($gen->send('something'));

    }

    private function gen()
    {
        yield 'foo';
        yield 'bar';
    }
    
    private function xrange($start, $end, $step = 1)
    {
        for ($i = $start; $i <= $end; $i += $step) {
            yield $i;
        }
    }

    public function getTaskId()
    {
        return new SystemCall(function (Task $task, Scheduler $scheduler) {
            $task->setSendValue($task->getTaskId());
            $scheduler->schedule($task);
        });
    }


    public function task()
    {
        $tid = (yield $this->getTaskId());
        $childTid = (yield $this->newTask($this->childTask()));

        for ($i=1; $i <= 6; ++$i) {
            echo "Parent task $tid iteration $i.<br/>";
            yield;

            if ($i == 3) yield $this->killTask($childTid);
        }
    }

    public function newTask(\Generator $coroutine)
    {
        return new SystemCall(
            function(Task $task, Scheduler $scheduler) use ($coroutine) {
                $task->setSendValue($scheduler->newTask($coroutine));
                $scheduler->schedule($task);
            }
        );
    }
    
    public function killTask($tid)
    {
        return new SystemCall(
          function(Task $task, Scheduler $scheduler) use ($tid) {
              $task->setSendValue($scheduler->killTask($tid));
              $scheduler->schedule($task);
          }  
        );
    }

    public function childTask()
    {
        $tid = (yield $this->getTaskId());
        while(true) {
            echo "Child task $tid still alive!<br/>";
            yield;
        }
    }

    private function waitForRead($socket)
    {
        return new SystemCall(
            function(Task $task, Scheduler $scheduler) use ($socket) {
                $scheduler->waitForRead($socket, $task);
            }
        );
    }

    private function waitForWrite($socket)
    {
        return new SystemCall(
            function(Task $task, Scheduler $scheduler) use ($socket) {
                $scheduler->waitForWrite($socket, $task);
            }
        );
    }

    public function server($port)
    {
        echo "Starting server at port $port...\n";

        $socket = @stream_socket_server("tcp://localhost:$port", $errNo, $errStr);
        if (!$socket) throw new \Exception($errStr, $errNo);

        stream_set_blocking($socket, 0);

        while(true) {
            yield $this->waitForRead($socket);
            $clientSocket = stream_socket_accept($socket, 0);
            yield newTask($this->handleClient($clientSocket));
        }
    }

    public function handleClient($socket)
    {
        yield $this->waitForRead($socket);
        $data = fread($socket, 8192);

        $msg = "Received folloeing request:\n\n$data";
        $msgLength = strlen($msg);

        $response = <<<RES
HTTP/1.1 200 OK\r
Content-Type:text/plain\r
Content-Length:$msgLength\r
Connection:close\r
\r
$msg
RES;
        yield $this->waitForWrite($socket);
        fwrite($socket, $response);
        fclose($socket);
    }
}
