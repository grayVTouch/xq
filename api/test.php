<?php

use Core\Lib\File;
use Core\Lib\Http;
use Core\Lib\M3U8;
use Core\Wrapper\FFmpeg;
use Core\Wrapper\FFprobe;
use function core\current_datetime;
use function core\format_time;
use function core\get_extension;
use function core\get_filename;
use function core\random;

require_once __DIR__ . '/app/Customize/api/admin/plugin/extra/app.php';




//$file = 'D:\web\xinqu\resource\upload\开发模式\专题视频\魔法少女 梅露露\0001【原画】.mp4';
//$file = 'E:\myself\human\二次元\魔法少女梅露露\魔法少女 梅露露 (1).mkv';
$file = 'G:\pornhub\temp_ts_CDQFLI\sequence-37.ts';

$info = FFprobe::create($file)
        ->coreInfo();

print_r($info);

