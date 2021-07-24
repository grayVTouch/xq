<?php


namespace App\Customize\api\admin\util;


class Util
{
    private static $maxExecutionTime = 30;

    private static $memoryLimit = '128M';

    public static function systemPowerUp()
    {
        /**
         * ******************
         * 提升各项性能指标
         * ******************
         */
        self::$maxExecutionTime = ini_get('max_execution_time');
        self::$memoryLimit = ini_get('memory_limit');

        // 不设限制
        ini_set('max_execution_time' , 0);
        ini_set('memory_limit' , -1);
    }

    public static function systemPowerDown()
    {
        /**
         * ****************************
         * 恢复各项性能指标
         * ****************************
         */
        ini_set('max_execution_time' , self::$maxExecutionTime);
        ini_set('memory_limit' , self::$memoryLimit);
    }
}
