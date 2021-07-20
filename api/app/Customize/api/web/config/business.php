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

    'type_for_video_project' => [
        'pro' => '专题' ,
        'misc' => '杂类' ,
    ] ,

    'status_for_image_project' => [
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

    'action_for_collection' => [
        // 收藏
        'collect' ,
        // 取消收藏
        'cancel' ,
    ] ,

    'mode_for_image_project' => [
        // 收藏
        'strict' ,
        // 取消收藏
        'loose' ,
    ] ,

    'mode_for_video_project' => [
        // 收藏
        'strict' ,
        // 取消收藏
        'loose' ,
    ] ,

    'content_type' => [
        'image_project' => '图片专题' ,
        'video_project' => '视频专题' ,
        'image' => '独立图片' ,
        'video' => '独立视频' ,
    ] ,

    'content_type' => [
        'image_project' => '图片专题' ,
        'video_project' => '视频专题' ,
        'image' => '独立图片' ,
        'video' => '独立视频' ,
    ] ,

    'relation_type_for_praise' => [
        'image_project' => '图片专题' ,
        'video_project' => '视频专题' ,
        'article_subject' => '文章专题' ,
    ] ,

    'relation_type_for_tag' => [
        'image_project' => '图片专题' ,
        'video_project' => '视频专题' ,
        'article_subject' => '文章专题' ,
    ] ,

    'platform' => [
        'web' =>'web端' ,
        'app' => 'app' ,
        'android' => 'android' ,
        'ios' => 'ios' ,
        'mobile' => '移动端' ,
    ] ,

    'email_code_type' => [
        'password' => '修改密码' ,
        'register' => '用户注册' ,
    ] ,

    'status_for_video_project' => [
        'making' => '连载中' ,
        'completed' => '已完结' ,
        'terminated' => '已终止' ,
    ] ,

    'mode_for_file' => [
        'ratio' ,
        'fix' ,
        'fix-width' ,
        'fix-height' ,
    ] ,

    'content_type' => [
        'image_project' => '图片专题' ,
        'video_project' => '视频专题' ,
        'image' => '图片' ,
        'video' => '视频' ,
    ] ,
];
