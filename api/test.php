<?php


//$total = 100;
//for ($i = 1; $i <= $total; $i++) {
//    // \r - return 移动到当前行的最左边
//    // \n - newline 新建一行
//    printf("progress: [%-50s] %d%% Done\r", '###' , $i/$total*100);
//    usleep(100 * 1000);
//}
//echo "\n";
//echo "Done!\n";

use Core\Lib\ImageProcessor;

//require __DIR__ . '/app/Customize/api/admin/plugin/extra/app.php' ;
//
//
//$s = "G:\resource\开发模式\非专题视频\20210716\guochan2048.com -超人气91逆天高颜值美少女 ▌多乙 ▌极品红衣尤物性感粉嫩名器 超细腻4K画质感受最顶级视觉盛宴";
//
//var_dump(str_replace('\\' , '/' , $s));


$url = 'http://running-xinqu.oss-cn-hangzhou.aliyuncs.com/20210721/%E7%8E%8B%E6%A2%93%E7%AB%A5.jpg';

$res = parse_url($url);

print_r($res);

//
//$index = mb_strpos($url , '/');
//$pathname = mb_substr($url , $index + 1);
//
//var_dump($pathname);
