<?php

/**
 * 个人自定义配置文件
 *
 * 由于通过 php artisan config:cache 命令缓存的配置文件
 * 仅在当前目录下存在的内容才会缓存
 * 所以，可以在 .env 新增自定义配置值
 * 然后在该文件内新增并设置
 * 尔后，就可以通过 config() 函数获取配置值
 */

return [

    // 资源路径
    'res_url' => env('APP_RES_URL' , '') ,

    // 内部访问资源
    'res_dir' => env('APP_RES_DIR' , __DIR__ . '/../../resource') ,

    // 后台用户：用户名
    'admin_username' => env('ADMIN_USERNAME' , 'admin') ,

    // 后台用户：密码
    'admin_password' => env('ADMIN_PASSWORD' , 'admin') ,

    // 平台用户：用户名
    'client_username' => env('CLIENT_USERNAME' , 'admin') ,

    // 平台用户：密码
    'client_password' => env('CLIENT_PASSWORD' , 'admin') ,
];
