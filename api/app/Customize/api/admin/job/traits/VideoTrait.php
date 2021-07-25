<?php


namespace App\Customize\api\admin\job\traits;


use function core\format_path;
use function core\random;

trait VideoTrait
{
    // 生成媒体的后缀
    private function generateMediaSuffix(string $type , string $name , string $extension): string
    {
        return $type === 'pro' ? $name . '.' . $extension : $name . '【' . random(8 , 'letter' , true) . '】' . '.' . $extension;
    }

    private function generateVideoMediaSuffix(string $type , string $definition , ?int $index , string $name , string $extension): string
    {
        if ($type === 'misc') {
            return $name . '【' . $definition . '】' . '【' . random(8 , 'letter' , true) . '】' . '.' . $extension;
        }
        if ($index < 10) {
            $index = '000' . $index;
        } else if ($index < 100) {
            $index = '00' . $index;
        } else if ($index < 1000) {
            $index = '0' . $index;
        } else {
            // 其他
        }
        $name = empty($name) ? $index : $name;
        return $name . '【' . $definition . '】 ' . '.' . $extension;
    }

    private function getVideoName($type , $name , $index)
    {
        if ($type === 'pro') {
            // [sprintf 函数可访问右侧链接](https://www.runoob.com/php/func-string-sprintf.html)
            return empty($name) ? sprintf("%'04s" , $index) : $name;
        }
        return $name;
    }
}
