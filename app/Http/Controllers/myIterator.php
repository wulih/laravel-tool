<?php
/**
 * Created by PhpStorm.
 * User: gokuai
 * Date: 17/8/31
 * Time: 14:03
 */

namespace App\Http\Controllers;


class myIterator implements \Iterator
{
    private $position = 0;
    private $array = array(
        "firstelement",
        "secondelement",
        "lastelement"
    );

    public function __construct()
    {
        $this->position = 0;
    }

    function rewind()
    {
        var_dump(__METHOD__);
        $this->position = 0;
    }

    function current()
    {
        var_dump(__METHOD__);
        return $this->array[$this->position];
    }

    function key()
    {
        var_dump(__METHOD__);
        return $this->position;
    }

    function next()
    {
        var_dump(__METHOD__);
        ++ $this->position;
    }
    
    function valid()
    {
        var_dump(__METHOD__);
        return isset($this->array[$this->position]);
    }
}