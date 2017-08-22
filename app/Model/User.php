<?php

namespace App\Model;

class User extends BaseModel
{
    protected $table = 'users';

    public function __call($name, $arguments)
    {
        $result = call_user_func_array([$this, $name], $arguments);
        return $result ? : $this;
    }

    //静态的insert ignore
    public static function __callStatic($name, $arguments)
    {
        $self = new self();
        $result = call_user_func_array([$self, $name], $arguments);
        return $result ? : $self;
    }
}
