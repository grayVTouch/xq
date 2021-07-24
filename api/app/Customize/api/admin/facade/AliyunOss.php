<?php

namespace App\Customize\api\admin\facade;

use App\Customize\api\admin\lib\AliyunOss as AliyunOssLib;
use App\Customize\api\admin\model\SystemSettingsModel;

/**
 * Class AliyunOss
 *
 * @method static string generateFilename(string $extension)
 * @method static array upload(string $bucket , string $filename , string $file , array $options = [])
 * @method static array delete(string $bucket , string $url)
 * @method static array copy(string $from_bucket , string $from_name , string $to_bucket , string $to_name , array $options = [])
 * @method static string getPathname(string $url)
 * @method static bool isRepeat(string $from_bucket , string $from_name , string $to_bucket , string $to_name)
 *
 * @see \App\Customize\api\admin\lib\AliyunOss
 */
class AliyunOss
{

    /**
     * @var \App\Customize\api\admin\lib\AliyunOss
     */
    private static $instance;

    public static function __callStatic($method , $args)
    {
        if (!(self::$instance instanceof AliyunOssLib)) {
            $settings = SystemSettingsModel::first();
            self::$instance = new AliyunOssLib($settings->aliyun_key , $settings->aliyun_secret , $settings->aliyun_endpoint);
        }
        return call_user_func_array([self::$instance , $method] , $args);
    }
}
