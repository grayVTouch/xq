<?php


namespace App\Http\Controllers\api\admin;


use App\Customize\api\admin\job\TestJob;

class Test extends Base
{
    public function index()
    {
        TestJob::dispatch();
    }
}
