<?php

return [
    // 后台权限类型
    'type_for_admin_permission' => [
        'view' ,
        'api'
    ] ,

    'bool_for_int' => [
        0 => '否' ,
        1 => '是'
    ] ,

    'file' => [
        'jpg' ,
        'png' ,
        'bmp' ,
        'jpeg' ,
        'gif' ,
    ] ,

    // 图片专题分类类型
    'type_for_image_project' => [
        'pro' => '专题' ,
        'misc' => '杂类' ,
    ] ,

    // 图片专题分类类型
    'type_for_video' => [
        'pro' => '专题' ,
        'misc' => '杂类' ,
    ] ,

    'status_for_image_project' => [
        -1 => '审核失败',
        0 => '待审核' ,
        1 => '审核成功' ,
    ] ,

    'status_for_video' => [
        -1 => '审核失败',
        0 => '待审核' ,
        1 => '审核成功' ,
    ] ,

    'status' => [
        -1 => '审核失败',
        0 => '待审核' ,
        1 => '审核成功' ,
    ] ,

    'status_for_image_subject' => [
        -1 => '审核失败',
        0 => '待审核' ,
        1 => '审核成功' ,
    ] ,

    'sex' => [
        'male' => '男' ,
        'female' => '女' ,
        'secret' => '保密' ,
        'both' => '两性' ,
        'shemale' => '人妖' ,
    ] ,

    'platform' => [
        'web' =>'web端' ,
        'app' => 'app' ,
        'android' => 'android' ,
        'ios' => 'ios' ,
        'mobile' => '移动端' ,
    ] ,

    'mode_for_file' => [
        'ratio' ,
        'fix' ,
        'fix-width' ,
        'fix-height' ,
    ] ,

    'image_project_process_status' => [
        -1 => '处理失败' ,
        0 => '待处理' ,
        1 => '处理中' ,
        2 => '处理完成' ,
    ] ,

    'video_process_status' => [
        -1 => '处理失败' ,
        0 => '待处理' ,
        1 => '处理中' ,
        2 => '转码中' ,
        3 => '处理完成' ,
    ] ,

    'video_file_process_status' => [
        -1 => '处理失败' ,
        0 => '待处理' ,
        1 => '处理中' ,
        2 => '处理完成' ,
    ] ,

    'video_project_file_process_status' => [
        -1 => '处理失败' ,
        0 => '待处理' ,
        1 => '处理中' ,
        2 => '处理完成' ,
    ] ,

    'end_status_for_video_project' => [
        'making' => '连载中' ,
        'completed' => '已完结' ,
        'terminated' => '已终止' ,
    ] ,

    'type_for_region' => [
        'country'   => '国家' ,
        'state'     => '州|邦|省' ,
        'region'    => '地区' ,
    ] ,

    'os_for_disk' => [
        'windows'   => 'windows' ,
        'linux'     => 'linux' ,
        'mac'       => 'mac' ,
    ] ,

    'status_for_video_series' => [
        -1 => '审核失败',
        0 => '待审核' ,
        1 => '审核成功' ,
    ] ,

    'status_for_video_company' => [
        -1 => '审核失败',
        0 => '待审核' ,
        1 => '审核成功' ,
    ] ,

    'status_for_video_subject' => [
        -1 => '审核失败',
        0 => '待审核' ,
        1 => '审核成功' ,
    ] ,

    'status_for_tag' => [
        -1 => '审核失败',
        0 => '待审核' ,
        1 => '审核成功' ,
    ] ,

    'status_for_category' => [
        -1 => '审核失败',
        0 => '待审核' ,
        1 => '审核成功' ,
    ] ,

    'status_for_video_project' => [
        -1 => '审核失败',
        0 => '待审核' ,
        1 => '审核成功' ,
    ] ,

    // 图片专题分类类型
    'type_for_nav' => [
        'image_project' => '图片专题' ,
        'video_project' => '视频专题' ,
    ] ,

    // 内容分类
    'content_type' => [
        'video' => '视频',
        'video_project' => '视频专题' ,
        'image_project' => '图片专题' ,
        'image' => '图片' ,
    ] ,

    'disk' => [
        'local' => '本地存储' ,
        'aliyun' => '阿里云' ,
    ] ,
];
