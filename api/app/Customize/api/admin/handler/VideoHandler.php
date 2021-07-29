<?php


namespace App\Customize\api\admin\handler;


use App\Customize\api\admin\model\CategoryModel;
use App\Customize\api\admin\model\ModuleModel;
use App\Customize\api\admin\model\RelationTagModel;
use App\Customize\api\admin\model\VideoSrcModel;
use App\Customize\api\admin\model\VideoProjectModel;
use App\Customize\api\admin\model\UserModel;
use App\Customize\api\admin\model\VideoSubtitleModel;
use App\Customize\api\admin\model\Model;
use stdClass;

use function api\admin\get_config_key_mapping_value;
use function core\convert_object;
use function core\format_time;
use function core\object_to_array;

class VideoHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        $model->__duration__          = empty($model->duration) ? 0 : format_time($model->duration , 'HH:II:SS');
        $model->__type__              = get_config_key_mapping_value('business.type_for_video' , $model->type);
        $model->__status__            = get_config_key_mapping_value('business.status_for_video' , $model->status);
        $model->__video_process_status__    = get_config_key_mapping_value('business.video_process_status' , $model->video_process_status);
        $model->__file_process_status__    = get_config_key_mapping_value('business.video_file_process_status' , $model->file_process_status);

        return $model;
    }


    public static function user($model): void
    {
        if (empty($model)) {
            return ;
        }
        $user = property_exists($model , 'user') ? $model->user : UserModel::find($model->user_id);
        $user = UserHandler::handle($user);
        $model->user = $user;
    }

    public static function module($model): void
    {
        if (empty($model)) {
            return ;
        }
        $module = property_exists($model , 'module') ? $model->module : ModuleModel::find($model->module_id);
        $module = ModuleHandler::handle($module);
        $model->module = $module;
    }

    public static function category($model): void
    {
        if (empty($model)) {
            return ;
        }
        if ($model->type === 'pro') {
            $category = property_exists($model , 'category') ? $model->category : CategoryModel::find($model->category_id);
            $category = CategoryHandler::handle($category);
        } else {
            $category = null;
        }
        $model->category = $category;
    }

    public static function videoProject($model): void
    {
        if (empty($model)) {
            return ;
        }
        if ($model->type === 'pro') {
            $video_project = property_exists($model , 'video_project') ? $model->video_project : VideoProjectModel::find($model->video_project_id);
            $video_project = VideoProjectHandler::handle($video_project);
        } else {
            $video_project = null;
        }
        $model->video_project = $video_project;
    }

    public static function videos($model): void
    {
        if (empty($model)) {
            return ;
        }
        $videos = property_exists($model , 'videos') ? $model->videos : VideoSrcModel::getByVideoId($model->id);
        $videos = VideoSrcHandler::handleAll($videos);
        $model->videos = $videos;
    }

    public static function videoSubtitles($model): void
    {
        if (empty($model)) {
            return ;
        }
        $video_subtitles = property_exists($model , 'video_subtitles') ? $model->video_subtitles : VideoSubtitleModel::getByVideoId($model->id);
        $video_subtitles = VideoSubtitleHandler::handleAll($video_subtitles);
        $model->video_subtitles = $video_subtitles;
    }

    public static function tags($model): void
    {
        if (empty($model)) {
            return ;
        }
        $tags = RelationTagModel::getByRelationTypeAndRelationId('video' , $model->id);
        $model->tags = $tags;
    }
}
