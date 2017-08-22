<?php

namespace App\Http\Controllers;

use App\Model\User;

class UserController extends Controller
{
    public function test()
    {
        $data = ['name'=>'Json', 'email' => '188@test.com', 'password'=>'123456'];

        //new对象
        $user = new User();
        $user->insertIgnore($data);

        //静态方法调用
        User::insertIgnore($data);
    }
}
