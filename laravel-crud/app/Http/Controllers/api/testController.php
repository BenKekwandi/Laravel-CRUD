<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class testController extends Controller
{
    public function index(){

        $data = Array(
            "message"=> "Welcome"
        );

        return response()->json($data);
    }
    public function test()
    {
        $data = "aaaaaaaaaaaaaaaaaaaaaaaa";
        Redis::set("test", $data);
        return response()->json($data);
    }

    public function sessionTest()
    {
        $data = "aaaaaaaaaaaaaaaaaaaaaaaa";
        return response()->json($data);
    }
}
