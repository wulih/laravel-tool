<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConnectorController extends Controller
{
    public function connector()
    {
        echo "1+5=" . 1+5; //用.连接符,输出为6
        echo "<br/>";
        echo "1+5=" , 1+5; //用,连接符,输出为1+5=6
    }
}
