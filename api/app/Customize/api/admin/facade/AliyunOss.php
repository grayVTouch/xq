<?php

namespace App\Customize\api\admin\facade;

use App\Customize\api\admin\lib\AliyunOss as AliyunOssLib;

/**
 * Class AliyunOss
 *
 * @method array upload(string $bucket , string $filename , string $file , array $options = [])
 * @method array delete(string $bucket , string $url)
 *
 * @see \App\Customize\api\admin\lib\AliyunOss
 */
class AliyunOss
{

    private static $instance;

    public static function __callStatic($method , $args)
    {
        if (!(self::$instance instanceof AliyunOssLib)) {
            self::$instance = new AliyunOssLib('LTAI7F0gAA1b6YXI' , 'XqWmTvjPm0adiaE1e1s3M61bHn22Yd' , 'http://oss-cn-hangzhou.aliyuncs.com');
        }
        call_user_func_array([self::$instance , $method] , $args);
    }
}
