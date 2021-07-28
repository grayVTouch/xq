<?php


namespace App\Http\Controllers;


class Test extends Controller
{
    public function one()
    {
//        var_dump('我非常好');
        var_dump(request()->post());
    }

    public function two()
    {
        var_dump('wohenhao !!!');
    }

    public function test()
    {
//        var_dump('test method!!!');
        phpinfo();
    }
}
