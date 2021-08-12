<?php

use Core\Lib\File;
use Core\Lib\Http;
use Core\Lib\M3U8;
use Core\Wrapper\FFmpeg;
use Core\Wrapper\FFprobe;
use function core\current_datetime;
use function core\detect_encoding;
use function core\format_time;
use function core\get_extension;
use function core\get_filename;
use function core\random;
use function core\remove_bom_header;

require_once __DIR__ . '/app/Customize/api/admin/plugin/extra/app.php';


$a = 0;
$b = 39;

var_dump(ratio($a , $b));

// 获取 增加/减少 百分比
function ratio($a , $b): string
{
    if ($b == 0) {
        var_dump('1');
        $ratio = bcmul($a , 100 , 2);
    } else {
        $amount = bcsub($a  , $b);
        $ratio = bcdiv($amount , $b , 4);
        $ratio = bcmul($ratio , 100 , 2);
    }
    $ratio = abs($ratio);
    return sprintf('%s%%' , $ratio);
}
