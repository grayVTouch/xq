<?php


namespace App\Http\Controllers\api\admin;


use App\Customize\api\admin\job\TestJob;

class Test extends Base
{
    public function index()
    {
//        TestJob::dispatch();
        $d = 10;
        $i = 0;
        while ($i++ < $d)
        {
            sleep(1);
        }
        echo '30s 过去，你看到我了';
    }
}
