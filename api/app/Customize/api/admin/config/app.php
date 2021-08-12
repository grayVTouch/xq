<?php

return [
    /**
     * 当前的磁盘存储方式
     *
     * local - 本地磁盘
     * cloud - 云存储
     */
    'disk' => 'local' ,

    // 相关目录
    'dir' => [
        'image'         => '非专题图片' ,
        'video'         => '非专题视频' ,
        'system'        => '系统资源' ,
        'image_project' => '专题图片' ,
        'video_project' => '专题视频' ,
    ] ,

    // 每页显示记录数
    'limit' => 20 ,

    // 视频第一帧截取的时间点（秒数）
    'video_frist_frame_duration' => 10 ,

    'video_first_frame' => [
        'duration'  => 10 ,
        'width'     => 1920 ,
    ] ,

    // 预览视频截取段数
    'video_simple_preview' => [
        // 截取的段数
        'count' => 5 ,
        // 截取的时间长度
        'duration' => 2 ,
        // 单张图片尺寸：宽
        'width' => 320 ,
        // 单张图片尺寸：高
        'height' => 180 ,
    ] ,

    // 视频预览单张间隔时间
    'video_preview' => [
        // 间隔 ?s 截取一张（推荐每秒一张）
        'duration' => 1 ,
        // 单张图片尺寸：宽
        'width' => 160 ,
        // 单张图片尺寸：高
        'height' => 90 ,
        // 单行显示数量
        'count' => 5 ,
    ] ,

    // 视频转码
    'video_transcoding' => [
        'codec'         => 'h264' ,
        // ffmpeg h.264 格式的视频处理要求 宽高都必须是 2 的倍数
        'specification' => [
//            '360P' => [
//                'w' => 600 ,
//                'h' => 360 ,
//                'is_hd' => false ,
//            ]  ,
            '480P' => [
                'w' => 640 ,
                'h' => 480 ,
                'is_hd' => false ,
            ]  ,
//            '720P' => [
//                'w' => 1280 ,
//                'h' => 720 ,
//                'is_hd' => false ,
//            ]  ,
//            '1080P' => [
//                'w' => 1920 ,
//                'h' => 1080 ,
//                'is_hd' => true ,
//            ]  ,
//            '2K' => [
//                'w' => 2048 ,
//                'h' => 1080 ,
//                'is_hd' => true ,
//            ]  ,
//            '4K' => [
//                'w' => 4096 ,
//                'h' => 2160 ,
//                'is_hd' => true ,
//            ]  ,
        ]
    ] ,

    // 视频处理后文件临时存放地址
    'video_temp_dir' => storage_path('app/video') ,

    // 队列超时时间
    'queue_timeout' => 365 * 24 * 3600 ,

    // 是否保存原视频（建议保留原视频 - 否则会影响重新处理视频的功能）
    'save_origin_video' => true ,

    // 字幕转换的格式
    'video_subtitle' => [
        'extension' => 'vtt' ,
    ] ,
];
