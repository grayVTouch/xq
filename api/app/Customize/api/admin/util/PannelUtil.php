<?php


namespace App\Customize\api\admin\util;


class PannelUtil
{
    // 获取 增长/减少 标志
    public static function flag($a , $b): string
    {
        return $a - $b >= 0 ? 'up' : 'down';
    }

    // 获取 增加/减少 百分比
    public static function ratio($a , $b): string
    {
        if ($b == 0) {
            $ratio = bcmul($a , 100 , 2);
        } else if ($a == 0) {
            $ratio = bcmul($b , 100 , 2);
        } else {
            $amount = bcsub($a  , $b);
            $ratio = bcdiv($amount , $b , 4);
            $ratio = bcmul($ratio , 100 , 2);
        }
        $ratio = abs($ratio);
        return sprintf('%s%%' , $ratio);
    }
}
