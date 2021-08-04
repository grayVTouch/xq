<?php

var_dump(file_exists("G:/resource/系统资源/20210802/20210802223128homHvo.ass"));
exit;

// gb2312
//$gbk_file = 'D:\web\xinqu\resource\upload\系统资源\20210802\test\test.ass';
//$utf8_file = 'D:\web\xinqu\resource\upload\系统资源\20210802\test\test_utf8.ass';

$gbk_file = __DIR__ . '/gbk_test.ass';
$utf8_file = __DIR__ . '/utf8_test.ass';
$vtt_file = __DIR__ . '/test.vtt';
$c_file = __DIR__ . '/test_utf8.ass';
$a_file = __DIR__ . '/a_gbk.ass';

//$str = file_get_contents($gbk_file);
//$charset = detect_encoding($str);

//var_dump($charset);

//$convert_str = mb_convert_encoding($str , 'UTF-8' , $charset);
//file_put_contents($gbk_file , $convert_str);

//var_dump(detect_encoding($vtt_file));

var_dump(detect_encoding($c_file));
var_dump(detect_encoding($a_file));

//
//var_dump(detect_encoding($gbk_file));
//var_dump(detect_encoding($utf8_file));

//var_dump(mb_detect_encoding($gbk_file , [
//    'ASCII' ,
//    'GBK' ,
//    'UTF-8' ,
//]));
//var_dump(mb_detect_encoding($utf8_file , [
//    'ASCII' ,
//    'GBK' ,
//    'UTF-8' ,
//]));



/**
 * 检测文件编码
 * @param string $file 文件名称 或 纯字符串
 * @return string|null 返回 编码名 或 null
 */
function detect_encoding(string $file): string
{
    $list = [
        'UTF-8',
        'GBK' ,
        'ISO-8859-1'
    ];
    var_dump($file);
    $str = file_exists($file) ? file_get_contents($file) : $file;

    foreach ($list as $item)
    {
        $tmp = mb_convert_encoding($str, $item, $item);
        if (md5($tmp) == md5($str)) {
            return $item;
        }
    }
    return false;
}
