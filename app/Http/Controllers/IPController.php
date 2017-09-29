<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IPController extends Controller
{
    public function index()
    {
        $place = '未知';
        $location = \IPLocation::find('127.0.0.1');
        if (($location !== 'N/A') && (!empty($location[2]))) {
            $place = $location[2];
        }

        echo $place;
    }
}
