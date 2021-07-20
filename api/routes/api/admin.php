<?php

use App\Customize\api\admin\middleware\CustomizeMiddleware;
use App\Customize\api\admin\middleware\UserAuthMiddleware;
use App\Http\Controllers\api\admin\AdminPermission;
use App\Http\Controllers\api\admin\Category;
use App\Http\Controllers\api\admin\Disk;
use App\Http\Controllers\api\admin\File;
use App\Http\Controllers\api\admin\ImageAtPosition;
use App\Http\Controllers\api\admin\ImageProject;
use App\Http\Controllers\api\admin\ImageSubject;
use App\Http\Controllers\api\admin\Job;
use App\Http\Controllers\api\admin\Login;
use App\Http\Controllers\api\admin\Misc;
use App\Http\Controllers\api\admin\Module;
use App\Http\Controllers\api\admin\Nav;
use App\Http\Controllers\api\admin\Pannel;
use App\Http\Controllers\api\admin\Position;
use App\Http\Controllers\api\admin\Region;
use App\Http\Controllers\api\admin\Role;
use App\Http\Controllers\api\admin\SystemDisk;
use App\Http\Controllers\api\admin\SystemSettings;
use App\Http\Controllers\api\admin\Tag;
use App\Http\Controllers\api\admin\User;
use App\Http\Controllers\api\admin\video;
use App\Http\Controllers\api\admin\VideoCompany;
use App\Http\Controllers\api\admin\VideoProject;
use App\Http\Controllers\api\admin\VideoSeries;
use App\Http\Controllers\api\admin\VideoSubtitle;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\api\admin\Admin;
use App\Http\Controllers\api\admin\Test;

Route::prefix('admin')
    ->middleware([
        CustomizeMiddleware::class
    ])
    // 明明路由前缀（避免冲突）
    ->name('api.admin.')
    ->group(function(){
        Route::middleware([])
            ->group(function(){
                Route::any('test' , [Test::class , 'index']);
                Route::any('test' , [Test::class , 'index']);


                // 不用登录的相关接口
                Route::get('captcha'    , [Misc::class , 'captcha'])->name('captcha');
                Route::post('login'     , [Login::class , 'login'])->name('login');
                Route::get('avatar'     , [Login::class , 'avatar'])->name('avatar');

                Route::post('upload'            , [File::class , 'upload'])->name('upload');
                Route::post('upload_image'      , [File::class , 'uploadImage'])->name('upload_image');
                Route::post('upload_video'      , [File::class , 'uploadVideo'])->name('upload_video');
                Route::post('upload_subtitle'   , [File::class , 'uploadSubtitle'])->name('upload_subtitle');
                Route::post('upload_office'     , [File::class , 'uploadOffice'])->name('upload_office');

                // 系统设置
                Route::get('login_settings' , [SystemSettings::class , 'loginSettings']);
            });

        Route::middleware([
            UserAuthMiddleware::class
        ])->group(function(){
            // 要求用户登录的相关接口

            /**
             * *****************************
             * 后台用户
             * *****************************
             */
            Route::get('info'           , [Admin::class , 'info'])->name('admin_info');
            Route::get('search_admin'   , [Admin::class , 'search'])->name('search_admin');
            Route::get('admin'          , [Admin::class , 'index'])->name('admin_index');
            Route::get('admin/{id}'     , [Admin::class , 'show'])->name('admin_show');
            Route::put('admin/{id}'     , [Admin::class , 'update'])->name('admin_update');
            Route::post('admin'         , [Admin::class , 'store'])->name('admin_store');
            // 特别注意，这边这个顺序不能更换
            // 如果更换会导致 路由匹配出现不是期望的现象
            Route::delete('destroy_all_admin'   , [Admin::class , 'destroyAll']);
            Route::delete('admin/{id}'          , [Admin::class , 'destroy']);

            /**
             * ********************
             * 用户相关
             * ********************
             */
            Route::get('search_user'    , [User::class , 'search']);
            Route::get('user'           , [User::class , 'index']);
            Route::get('user/{id}'      , [User::class , 'show']);
            Route::put('user/{id}'      , [User::class , 'update']);
            Route::post('user'          , [User::class , 'store']);
            // 特别注意，这边这个顺序不能更换
            // 如果更换会导致 路由匹配出现不是期望的现象
            Route::delete('destroy_all_user'    , [User::class , 'destroyAll']);
            Route::delete('user/{id}'           , [User::class , 'destroy']);

            /**
             * 权限管理
             */
            Route::get('admin_permission'           , [AdminPermission::class , 'index']);
            Route::get('admin_permission/{id}'      , [AdminPermission::class , 'show']);
            Route::get('admin_permission/{id}/all'  , [AdminPermission::class , 'allExcludeSelfAndChildren']);
            Route::patch('admin_permission/{id}'    , [AdminPermission::class , 'localUpdate']);
            Route::put('admin_permission/{id}'      , [AdminPermission::class , 'update']);
            Route::post('admin_permission'          , [AdminPermission::class , 'store']);
            // 特别注意，这边这个顺序不能更换
            // 如果更换会导致 路由匹配出现不是期望的现象
            Route::delete('destroy_all_admin_permission/'   , [AdminPermission::class , 'destroyAll']);
            Route::delete('admin_permission/{id}'           , [AdminPermission::class , 'destroy']);

            /**
             * ******************
             * 角色管理
             * ******************
             */
            Route::get('role'                   , [Role::class , 'index']);
            Route::get('role/{id}'              , [Role::class , 'show']);
            Route::get('role/{id}/permission'   , [Role::class , 'permission']);
            Route::patch('role/{id}'            , [Role::class , 'localUpdate']);
            Route::put('role/{id}'              , [Role::class , 'update']);
            Route::post('role'                  , [Role::class , 'store']);
            // 特别注意，这边这个顺序不能更换
            // 如果更换会导致 路由匹配出现不是期望的现象
            Route::delete('destroy_all_role'    , [Role::class , 'destroyAll']);
            Route::delete('role/{id}'           , [Role::class , 'destroy']);
            Route::post('role/{id}/allocate_permission' , [Role::class , 'allocatePermission']);
            Route::get('all_role'                       , [Role::class , 'all']);


            /**
             * ******************
             * 模块管理
             * ******************
             */
            Route::get('module'         , [Module::class , 'index']);
            Route::get('module/{id}'    , [Module::class , 'show']);
            Route::patch('module/{id}'  , [Module::class , 'localUpdate']);
            Route::put('module/{id}'    , [Module::class , 'update']);
            Route::post('module'        , [Module::class , 'store']);
            // 特别注意，这边这个顺序不能更换
            // 如果更换会导致 路由匹配出现不是期望的现象
            Route::delete('destroy_all_module'  , [Module::class , 'destroyAll']);
            Route::delete('module/{id}'         , [Module::class , 'destroy']);
            Route::get('get_all_module'         , [Module::class , 'all']);


            /**
             * ******************
             * 标签管理
             * ******************
             */
            Route::get('tag'            , [Tag::class , 'index']);
            Route::get('tag/{id}'       , [Tag::class , 'show']);
            Route::patch('tag/{id}'     , [Tag::class , 'localUpdate']);
            Route::put('tag/{id}'       , [Tag::class , 'update']);
            Route::post('tag'           , [Tag::class , 'store']);
            Route::post('find_or_create_tag' , [Tag::class , 'findOrCreate']);
            // 特别注意，这边这个顺序不能更换
            // 如果更换会导致 路由匹配出现不是期望的现象
            Route::delete('destroy_all_tag' , [Tag::class , 'destroyAll']);
            Route::delete('tag/{id}'        , [Tag::class , 'destroy']);
            Route::get('search_tag'         , [Tag::class , 'search']);
            Route::get('top_tags'   , [Tag::class , 'top']);


            /**
             * 分类管理
             */
            Route::get('category'                       , [Category::class , 'index']);
            Route::get('search_category'                , [Category::class , 'search']);
            Route::get('category/{id}/all'              , [Category::class , 'allExcludeSelfAndChildren']);
            Route::get('category/{id}'                  , [Category::class , 'show']);
            Route::patch('category/{id}'                , [Category::class , 'localUpdate']);
            Route::put('category/{id}'                  , [Category::class , 'update']);
            Route::post('category'                      , [Category::class , 'store']);
            // 特别注意，这边这个顺序不能更换
            // 如果更换会导致 路由匹配出现不是期望的现象
            Route::delete('destroy_all_category'        , [Category::class , 'destroyAll']);
            Route::delete('category/{id}'               , [Category::class , 'destroy']);

            /**
             * ******************
             * 关联主体
             * ******************
             */
            Route::get('image_subject'          , [ImageSubject::class , 'index']);
            Route::get('image_subject/{id}'     , [ImageSubject::class , 'show']);
            Route::patch('image_subject/{id}'   , [ImageSubject::class , 'localUpdate']);
            Route::put('image_subject/{id}'     , [ImageSubject::class , 'update']);
            Route::post('image_subject'         , [ImageSubject::class , 'store']);
            // 特别注意，这边这个顺序不能更换
            // 如果更换会导致 路由匹配出现不是期望的现象
            Route::delete('destroy_all_image_subject'   , [ImageSubject::class , 'destroyAll']);
            Route::delete('image_subject/{id}'          , [ImageSubject::class , 'destroy']);
            Route::get('search_image_subject'           , [ImageSubject::class , 'search']);

            /**
             * ******************
             * 图片专题
             * ******************
             */
            Route::get('image_project'          , [ImageProject::class , 'index']);
            Route::get('image_project/{id}'     , [ImageProject::class , 'show']);
            Route::patch('image_project/{id}'   , [ImageProject::class , 'localUpdate']);
            Route::put('image_project/{id}'     , [ImageProject::class , 'update']);
            Route::post('image_project'         , [ImageProject::class , 'store']);
            // 特别注意，这边这个顺序不能更换
            // 如果更换会导致 路由匹配出现不是期望的现象
            Route::delete('destroy_all_image_project'   , [ImageProject::class , 'destroyAll']);
            Route::delete('image_project/{id}'          , [ImageProject::class , 'destroy']);
            Route::delete('destroy_all_image_for_image_project' , [ImageProject::class , 'destroyImages']);
            Route::delete('destroy_image_project_tag'           , [ImageProject::class , 'destroyTag']);

            /**
             * ******************
             * 系统位置
             * ******************
             */
            Route::get('position'           , [Position::class , 'index']);
            Route::get('position/{id}'      , [Position::class , 'show']);
            Route::patch('position/{id}'    , [Position::class , 'localUpdate']);
            Route::put('position/{id}'      , [Position::class , 'update']);
            Route::post('position'          , [Position::class , 'store']);


            // 特别注意，这边这个顺序不能更换
            // 如果更换会导致 路由匹配出现不是期望的现象
            Route::delete('destroy_all_position'    , [Position::class , 'destroyAll']);
            Route::delete('position/{id}'           , [Position::class , 'destroy']);
            Route::get('search_position'            , [Position::class , 'search']);
            Route::get('get_all_position'           , [Position::class , 'all']);

            /**
             * ******************
             * 定点图片
             * ******************
             */
            Route::get('image_at_position'          , [ImageAtPosition::class , 'index']);
            Route::get('image_at_position/{id}'     , [ImageAtPosition::class , 'show']);
            Route::patch('image_at_position/{id}'   , [ImageAtPosition::class , 'localUpdate']);
            Route::put('image_at_position/{id}'     , [ImageAtPosition::class , 'update']);
            Route::post('image_at_position'         , [ImageAtPosition::class , 'store']);
            // 特别注意，这边这个顺序不能更换
            // 如果更换会导致 路由匹配出现不是期望的现象
            Route::delete('destroy_all_image_at_position'   , [ImageAtPosition::class , 'destroyAll']);
            Route::delete('image_at_position/{id}'          , [ImageAtPosition::class , 'destroy']);

            /**
             * 导航菜单
             */
            Route::get('nav'            , [Nav::class , 'index']);
            Route::get('search_nav'     , [Nav::class , 'search']);
            Route::get('nav/{id}'       , [Nav::class , 'show']);
            Route::patch('nav/{id}'     , [Nav::class , 'localUpdate']);
            Route::put('nav/{id}'       , [Nav::class , 'update']);
            Route::post('nav'           , [Nav::class , 'store']);
            // 特别注意，这边这个顺序不能更换
            // 如果更换会导致 路由匹配出现不是期望的现象
            Route::delete('destroy_all_nav'     , [Nav::class , 'destroyAll']);
            Route::delete('nav/{id}'            , [Nav::class , 'destroy']);


            /**
             * ******************
             * 视频
             * ******************
             */
            Route::get('video'              , [Video::class , 'index']);
            Route::get('video/{id}'         , [Video::class , 'show']);
            Route::patch('video/{id}'       , [Video::class , 'localUpdate']);
            Route::put('video/{id}'         , [Video::class , 'update']);
            Route::post('video'             , [Video::class , 'store']);
            Route::delete('video/{id}'      , [Video::class , 'destroy']);
            Route::delete('destroy_all_video'   , [Video::class , 'destroyAll']);
            Route::delete('destroy_videos'      , [Video::class , 'destroyVideos']);
            Route::post('retry_process_video'   , [Video::class , 'retry']);
            Route::delete('destroy_video_tag'   , [Video::class , 'destroyTag']);

            /**
             * ******************
             * 视频专题
             * ******************
             */
            Route::get('video_project'          , [VideoProject::class , 'index']);
            Route::get('video_project/{id}'     , [VideoProject::class , 'show']);
            Route::patch('video_project/{id}'   , [VideoProject::class , 'localUpdate']);
            Route::put('video_project/{id}'     , [VideoProject::class , 'update']);
            Route::post('video_project'         , [VideoProject::class , 'store']);
            // 特别注意，这边这个顺序不能更换
            // 如果更换会导致 路由匹配出现不是期望的现象
            Route::delete('destroy_all_video_project'   , [VideoProject::class , 'destroyAll']);
            Route::delete('video_project/{id}'          , [VideoProject::class , 'destroy']);
            Route::get('search_video_project'           , [VideoProject::class , 'search']);
            Route::delete('destroy_video_project_tag'   , [VideoProject::class , 'destroyTag']);

            /**
             * ******************
             * 视频系列
             * ******************
             */
            Route::get('video_series'           , [VideoSeries::class , 'index']);
            Route::get('video_series/{id}'      , [VideoSeries::class , 'show']);
            Route::patch('video_series/{id}'    , [VideoSeries::class , 'localUpdate']);
            Route::put('video_series/{id}'      , [VideoSeries::class , 'update']);
            Route::post('video_series'          , [VideoSeries::class , 'store']);
            // 特别注意，这边这个顺序不能更换
            // 如果更换会导致 路由匹配出现不是期望的现象
            Route::delete('destroy_all_video_series'    , [VideoSeries::class , 'destroyAll']);
            Route::delete('video_series/{id}'           , [VideoSeries::class , 'destroy']);
            Route::get('search_video_series'            , [VideoSeries::class , 'search']);

            /**
             * region 相关接口
             */
            Route::get('country'        , [Region::class , 'country']);
            Route::get('search_region'  , [Region::class , 'search']);

            /**
             * ******************
             * 视频制作公司
             * ******************
             */
            Route::get('video_company'              , [VideoCompany::class , 'index']);
            Route::get('video_company/{id}'         , [VideoCompany::class , 'show']);
            Route::patch('video_company/{id}'       , [VideoCompany::class , 'localUpdate']);
            Route::put('video_company/{id}'         , [VideoCompany::class , 'update']);
            Route::post('video_company'             , [VideoCompany::class , 'store']);
            // 特别注意，这边这个顺序不能更换
            // 如果更换会导致 路由匹配出现不是期望的现象
            Route::delete('destroy_all_video_company'   , [VideoCompany::class , 'destroyAll']);
            Route::delete('video_company/{id}'          , [VideoCompany::class , 'destroy']);
            Route::get('search_video_company'           , [VideoCompany::class , 'search']);

            /**
             * 控制台
             */
            Route::get('pannel_info' , [Pannel::class , 'info']);
            // 月统计资料
            Route::get('month_data' , [Pannel::class , 'monthData']);
            Route::get('quarter_data' , [Pannel::class , 'quarterData']);
            Route::get('year_data' , [Pannel::class , 'yearData']);

            /**
             * ******************
             * 磁盘管理
             * ******************
             */
            Route::get('disk'           , [Disk::class , 'index']);
            Route::get('disk/{id}'      , [Disk::class , 'show']);
            Route::patch('disk/{id}'    , [Disk::class , 'localUpdate']);
            Route::put('disk/{id}'      , [Disk::class , 'update']);
            Route::post('disk'          , [Disk::class , 'store']);
            // 特别注意，这边这个顺序不能更换
            // 如果更换会导致 路由匹配出现不是期望的现象
            Route::delete('destroy_all_disk'    , [Disk::class , 'destroyAll']);
            Route::delete('disk/{id}'           , [Disk::class , 'destroy']);
            Route::post('link_disk'             , [Disk::class , 'link']);


            /**
             * 磁盘选择
             */
            Route::get('system_disk' , [SystemDisk::class , 'index']);

            /***
             * 队列任务相关
             */
            Route::post('retry_job' , [Job::class , 'retry']);
            Route::post('flush_job' , [Job::class , 'flush']);
            Route::post('resource_clear_job' , [Job::class , 'resourceClear']);

            /**
             * 视频字幕相关
             */
            Route::delete('video_subtitle/{id}'         , [VideoSubtitle::class , 'destroy']);
            Route::delete('destroy_all_video_subtitle'  , [VideoSubtitle::class , 'destroyAll']);

            /**
             * ****************************
             * 系统设置
             * ****************************
             */
            Route::get('common_settings' , [SystemSettings::class , 'data']);
            Route::put('common_settings' , [SystemSettings::class , 'update']);
        });
    });
