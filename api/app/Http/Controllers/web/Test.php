<?php


namespace App\Http\Controllers\web;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

class Test extends Base
{
    public function index()
    {

    }

    public function welcome()
    {
        return view('welcome');
    }
}
