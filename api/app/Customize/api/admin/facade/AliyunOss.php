<?php

namespace App\Customize\api\admin\facade;

use App\Customize\api\admin\lib\AliyunOss as AliyunOssLib;
use App\Customize\api\admin\model\SystemSettingsModel;

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
