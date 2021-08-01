<?php

namespace App\Customize\api\admin\model;


class LogModel extends Model
{
    //
    protected $table = 'xq_log';

    public static function log(string $type , string $log , string $created_at): int
    {
        return self::insertGetId(compact('type' , 'log' , 'created_at'));
    }
}
