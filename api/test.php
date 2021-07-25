<?php


$arr = [
    '720P' => [
        'value' => 10
    ] ,

    '1080P' => [
        'value' => 20
    ] ,
];

$c_arr = $arr;

//print_r($arr);

usort($c_arr , function($a , $b){
    if ($a['value'] === $b['value']) {
        return 0;
    }
    return $a['value'] < $b['value'] ? 1 : -1;
});

print_r($arr);
print_r($c_arr);
