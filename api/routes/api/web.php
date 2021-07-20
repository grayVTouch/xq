<?php


use App\Customize\api\web\middleware\CustomizeMiddleware;
use App\Customize\api\web\middleware\UserAuthMiddleware;
use App\Customize\api\web\middleware\UserMiddleware;
use App\Http\Controllers\api\web\Category;
use App\Http\Controllers\api\web\File;
use App\Http\Controllers\api\web\Image;
use App\Http\Controllers\api\web\ImageAtPosition;
use App\Http\Controllers\api\web\ImageProject;
use App\Http\Controllers\api\web\Misc;
use App\Http\Controllers\api\web\Module;
use App\Http\Controllers\api\web\Nav;
use App\Http\Controllers\api\web\ImageSubject;
use App\Http\Controllers\api\web\Tag;
use App\Http\Controllers\api\web\User;
use App\Http\Controllers\api\web\Video;
use App\Http\Controllers\api\web\VideoCompany;
use App\Http\Controllers\api\web\VideoProject;
use App\Http\Controllers\api\web\VideoSeries;
use Illuminate\Support\Facades\Route;

Route::prefix('web')
    ->middleware([
        CustomizeMiddleware::class ,
    ])
    ->name('api.web.')
    ->group(function(){
        Route::middleware([
            UserMiddleware::class
        ])->group(function(){
            // 不用登录的相关接口

            // 文件上传
            Route::post('upload'        , [File::class      , 'upload']);
            // 上传图片
            Route::post('upload_image'  , [File::class      , 'uploadImage']);
            // 上传视频
            Route::post('upload_video'  , [File::class      , 'uploadVideo']);
            // 上传字幕
            Route::post('upload_subtitle' , [File::class    , 'uploadSubtitle']);
            // 上传办公文件
            Route::post('upload_office' , [File::class      , 'uploadOffice']);

            /***
             * *****************
             * 模块相关接口
             * *****************
             */

            // 所有模块
            Route::get('module'                     , [Module::class        , 'all']);
            // 默认模块
            Route::get('default_module'             , [Module::class        , 'default']);
            // 所有分类
            Route::get('category'                   , [Category::class      , 'all']);
            // 所有导航菜单
            Route::get('nav'                        , [Nav::class           , 'all']);
            // 首页幻灯片
            Route::get('home_slideshow'             , [ImageAtPosition::class , 'home']);
            // 图片专题幻灯片
            Route::get('image_project_slideshow'    , [ImageAtPosition::class , 'imageProject']);


            /**
             * ****************
             * 图片专题
             * ****************
             */

            // 图片专题 - 最新
            Route::get('image_project/newest'       , [ImageProject::class , 'newest']);
            // 图片专题 - 热门
            Route::get('image_project/hot'          , [ImageProject::class , 'hot']);
            // 图片专题 - 最新 - 分页
            Route::get('image_project/newest_with_pager'        , [ImageProject::class , 'newestWithPager']);
            // 图片专题 - 热门 - 分页
            Route::get('image_project/hot_with_pager'           , [ImageProject::class , 'hotWithPager']);
            // 图片专题 - 通过标签过滤
            Route::get('image_project/get_by_tag_id'   , [ImageProject::class , 'getByTagId']);
            // 图片专题 - 通过标签过滤 - 分页
            Route::get('image_project/get_with_pager_by_tag_ids' , [ImageProject::class , 'getWithPagerByTagIds']);
            // 图片专题 - 热门标签
            Route::get('image_project/hot_tags'             , [ImageProject::class , 'hotTags']);
            // 图片专题 - 热门标签 - 分页
            Route::get('image_project/hot_tags_with_pager'  , [ImageProject::class , 'hotTagsWithPager']);
            // 图片专题 - 分类
            Route::get('image_project/category'             , [ImageProject::class , 'category']);
            // 图片专题 - 推荐列表
            Route::get('image_project/{id}/recommend' , [ImageProject::class , 'recommend']);
            // 图片专题 - 图片主体
            Route::get('image_project/image_subject'  , [ImageProject::class , 'imageSubject']);
            // 团偏专题 - 详情
            Route::get('image_project/{id}'     , [ImageProject::class , 'show']);
            // 图片专题 - 列表
            Route::get('image_project'          , [ImageProject::class , 'index']);

            // 图片主体 - 详情
            Route::get('image_subject/{id}' , [ImageSubject::class , 'show']);
            // 图片专题 - 增加浏览量
            Route::post('image_project/{id}/increment_view_count' , [ImageProject::class , 'incrementViewCount']);

            // 分类 - 详情
            Route::get('category/{id}' , [Category::class , 'show']);

            // 标签 - 详情
            Route::get('tag/{id}' , [Tag::class , 'show']);

            // 验证码
            Route::get('captcha' , [Misc::class , 'captcha']);
            // 用户 - 登录
            Route::post('login' , [User::class , 'login']);
            // 用户 - 注册
            Route::post('register' , [User::class , 'store']);
            // 用户 - 更新密码
            Route::patch('user/update_password' , [User::class , 'updatePassword']);
            // 用户 - 发送更新密码的验证码
            Route::post('send_email_code_for_password' , [Misc::class , 'sendEmailCodeForPassword']);
            // 用户 - 发送注册的验证码
            Route::post('send_email_code_for_register' , [Misc::class , 'sendEmailCodeForRegister']);
            // 用户 - 我关注的用户
            Route::get('user/{id}/my_focus_user' , [User::class , 'myFocusUser']);
            // 用户 - 关注我的用户
            Route::get('user/{id}/focus_me_user' , [User::class , 'focusMeUser']);
            // 用户 - 我的收藏夹
            Route::get('user/{id}/collection_group' , [User::class , 'collectionGroupByUserId']);
            // 用户 - 详情
            Route::get('user/{id}/show' , [User::class , 'show']);
            // 用户 - 收藏夹信息
            Route::get('user/{collection_group_id}/collection_group_info' , [User::class , 'collectionGroupInfo']);
            // 用户 - 我的收藏
            Route::get('user/collections' , [User::class , 'collections']);

            /**
             * **********************
             * 杂项图片
             * **********************
             */
            // 图片 - 列表
            Route::get('image' , [Image::class , 'index']);
            // 图片-最新
            Route::get('image/newest' , [Image::class , 'newest']);
            // 图片 - 热门
            Route::get('image/hot'          , [Image::class , 'hot']);
            // 图片 - 通过标签过滤
            Route::get('image/get_by_tag_id'   , [Image::class , 'getByTagId']);
            // 图片 - 热门标签
            Route::get('image/hot_tags'             , [Image::class , 'hotTags']);
            // 图片 - 热门标签 - 分页
            Route::get('image/hot_tags_with_pager'  , [Image::class , 'hotTagsWithPager']);
            // 图片 - 分类
            Route::get('image/category'             , [Image::class , 'category']);
            // 图片 - 详情
            Route::get('image/{id}'     , [Image::class , 'show']);
            // 图片 - 列表
            Route::get('image'          , [Image::class , 'index']);
            // 图片 - 增加浏览量
            Route::post('image/{id}/increment_view_count' , [Image::class , 'incrementViewCount']);
            // 图片 - 推荐
            Route::get('image/{id}/recommend' , [Image::class , 'recommend']);

            /**
             * ************************************
             * 视频专题
             * ************************************
             */

            // 视频专题 - 列表
            Route::get('video_project' , [VideoProject::class , 'index']);
            // 视频专题 - 最新
            Route::get('video_project/newest' , [VideoProject::class , 'newest']);
            // 视频专题 - 热门标签
            Route::get('video_project/hot_tags' , [VideoProject::class , 'hotTags']);
            // 视频专题 - 热门
            Route::get('video_project/hot' , [VideoProject::class , 'hot']);
            // 视频专题 - 通过标签过滤
            Route::get('video_project/get_by_tag_id' , [VideoProject::class , 'getByTagId']);
            // 视频专题 - 通过标签过滤
            Route::get('video_project/get_by_tag_ids' , [VideoProject::class , 'getByTagIds']);
            // 视频专题 - 热门标签 - 分页
            Route::get('video_project/hot_tags_with_pager'  , [VideoProject::class , 'hotTagsWithPager']);
            // 视频专题-分类
            Route::get('video_project/category'             , [VideoProject::class , 'category']);
            // 视频专题 - 详情
            Route::get('video_project/{id}'        , [VideoProject::class , 'show']);
            // 视频专题 - 视频范围
            Route::get('video_project/{id}/videos_in_range' , [VideoProject::class , 'videosInRange']);
            // 视频专题 - 给定系列下的
            Route::get('video_project_filter_by_video_series_id' , [VideoProject::class , 'getByVideoSeriesId']);
            // 视频专题 - 视频系列
            Route::get('video_series' , [VideoSeries::class , 'index']);
            // 视频专题 - 视频系列 - 详情
            Route::get('video_series/{id}' , [VideoSeries::class , 'show']);
            // 视频专题 - 视频公司
            Route::get('video_company' , [VideoCompany::class , 'index']);
            // 视频专题 - 视频公司 - 详情
            Route::get('video_company/{id}' , [VideoCompany::class , 'show']);

            // 视频专题/视频 - 增加观看次数
            Route::post('video/{id}/increment_view_count' , [Video::class , 'incrementViewCount']);
            // 视频专题/视频 - 增加播放次数
            Route::post('video/{id}/increment_play_count' , [Video::class , 'incrementPlayCount']);

            /**
             * 杂项视频
             */
            // 视频 - 列表
            Route::get('video' , [Video::class , 'index']);
            // 视频 - 最新
            Route::get('video/newest' , [Video::class , 'newest']);
            // 视频 - 热门标签
            Route::get('video/hot_tags' , [Video::class , 'hotTags']);
            // 视频 - 热门
            Route::get('video/hot' , [Video::class , 'hot']);
            // 视频 - 通过标签过滤
            Route::get('video/get_by_tag_id' , [Video::class , 'getByTagId']);
            // 视频 - 通过标签过滤
            Route::get('video/get_by_tag_ids' , [Video::class , 'getByTagIds']);
            // 视频 - 通过标签过滤
            Route::get('video/get_by_tag_id' , [Video::class , 'getByTagId']);
            // 视频 - 通过标签过滤
            Route::get('video/get_by_tag_ids' , [Video::class , 'getByTagIds']);
            // 视频 - 热门标签 - 分页
            Route::get('video/hot_tags_with_pager'  , [Video::class , 'hotTagsWithPager']);
            // 视频-分类
            Route::get('video/category'             , [Video::class , 'category']);
            // 推荐视频
            Route::get('video/{id}/recommend'        , [Video::class , 'recommend']);
            // 视频 - 详情
            Route::get('video/{id}'        , [Video::class , 'show']);
        });

        Route::middleware([
            UserAuthMiddleware::class
        ])->group(function(){
            // 要求登录的相关接口

            // 用户 - 收藏动作
            Route::post('user/collection_handle'    , [User::class , 'collectionHandle']);
            // 用户 - 点赞动作
            Route::post('user/praise_handle'        , [User::class , 'praiseHandle']);
            // 用户 - 记录历史
            Route::post('user/record'               , [User::class , 'record']);
            // 用户 - 创建并加入收藏夹
            Route::post('user/create_and_join_collection_group' , [User::class , 'createAndJoinCollectionGroup']);
            // 用户 - 创建收藏夹
            Route::post('user/create_collection_group' , [User::class , 'createCollectionGroup']);
            // 用户 - 加入收藏夹
            Route::post('user/join_collection_group' , [User::class , 'joinCollectionGroup']);
            // 用户 - 删除收藏夹
            Route::delete('user/destroy_collection_group' , [User::class , 'destroyCollectionGroup']);
            // 用户 - 批量删除收藏夹
            Route::delete('user/destroy_all_collection_group' , [User::class , 'destroyAllCollectionGroup']);
            // 用户 - 删除收藏夹
            Route::delete('user/destroy_collection' , [User::class , 'destroyCollection']);
            // 用户 - 删除历史记录
            Route::delete('user/destroy_history' , [User::class , 'destroyHistory']);
            // 用户 - 删除点赞记录
            Route::delete('user/destroy_my_praise' , [User::class , 'destroyMyPraise']);
            // 用户 - 收藏夹列表（带判断 某事物是否存在于此收藏夹）
            Route::get('user/collection_group_with_judge' , [User::class , 'collectionGroupWithJudge']);
            // 用户 - 收藏夹列表
            Route::get('user/collection_group' , [User::class , 'collectionGroup']);
            // 用户 - 个人信息
            Route::get('user_info' , [User::class , 'info']);
            // 用户 - 历史记录 - 简要
            Route::get('less_history' , [User::class , 'lessHistory']);
            // 用户 - 历史记录 - 完整
            Route::get('history' , [User::class , 'histories']);
            // 用户 - 我的点赞记录
            Route::get('my_praise' , [User::class , 'myPraise']);
            // 用户 - 收藏夹（含收藏内容） - 简要
            Route::get('less_relation_in_collection' , [User::class , 'lessRelationInCollection']);
            // 用户 - 收藏夹（含收藏内容） - 简要
            Route::get('less_collection_group_with_collection' , [User::class , 'lessCollectionGroupWithCollection']);
            // 用户 - 更新用户信息
            Route::put('user' , [User::class , 'update']);
            // 用户 - 局部更新
            Route::patch('user' , [User::class , 'localUpdate']);
            // 用户 - 更新密码（用户已登录状态）
            Route::patch('user/update_password_in_logged' , [User::class , 'updatePasswordInLogged']);
            // 用户 - 更新收藏夹
            Route::patch('user/update_collection_group' , [User::class , 'updateCollectionGroup']);
            // 关注用户
            Route::post('user/focus_handle' , [User::class , 'focusHandle']);


            /**
             * 推按专题
             */
            // 视频专题 - 点赞（取消点赞）
            Route::post('image/{id}/praise_handle' , [Image::class , 'praiseHandle']);

            /**
             * 视频专题
             */
            // 视频专题 - 点赞（取消点赞）
            Route::post('video_project/{id}/praise_handle' , [VideoProject::class , 'praiseHandle']);

            /**
             * 视频（可能包含专题）
             */
            // 视频专题 - 记录播放情况
            Route::post('video/{id}/record' , [Video::class , 'record']);

            // 视频 - 点赞（取消点赞）
            Route::post('video/{id}/praise_handle' , [Video::class , 'praiseHandle']);
        });
    });
