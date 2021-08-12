<?php


namespace App\Customize\api\admin\handler;


use App\Customize\api\admin\model\CategoryModel;
use App\Customize\api\admin\model\ModuleModel;
use App\Customize\api\admin\model\RelationTagModel;
use App\Customize\api\admin\model\UserModel;
use App\Customize\api\admin\model\VideoCompanyModel;
use App\Customize\api\admin\model\VideoModel;
use App\Customize\api\admin\model\VideoSeriesModel;
use App\Customize\api\admin\model\Model;
use App\Customize\api\admin\model\VideoSubjectModel;
use stdClass;

use function api\admin\get_config_key_mapping_value;
use function core\convert_object;

class VideoProjectHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        $model->__end_status__  = empty($model->end_status) ? '' : get_config_key_mapping_value('business.end_status_for_video_project' , $model->end_status);
        $model->__status__      = empty($model->status) ? '' : get_config_key_mapping_value('business.status_for_video_project' , $model->status);
        $model->__file_process_status__    = get_config_key_mapping_value('business.video_project_file_process_status' , $model->file_process_status);

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

    public static function videoSeries($model): void
    {
        if (empty($model)) {
            return ;
        }
        $video_series = property_exists($model , 'video_series') ? $model->video_series : VideoSeriesModel::find($model->video_series_id);
        $video_series = VideoSeriesHandler::handle($video_series);
        $model->video_series = $video_series;
    }


    public static function videoCompany($model): void
    {
        if (empty($model)) {
            return ;
        }
        $video_company = property_exists($model , 'video_company') ? $model->video_company : VideoCompanyModel::find($model->video_company_id);
        $video_company = VideoCompanyHandler::handle($video_company);
        $model->video_company = $video_company;
    }

    public static function videoSubject($model): void
    {
        if (empty($model)) {
            return ;
        }
        $video_subject = property_exists($model , 'video_subject') ? $model->video_subject : VideoSubjectModel::find($model->video_subject_id);
        $video_subject = VideoSubjectHandler::handle($video_subject);
        $model->video_subject = $video_subject;
    }

    public static function category($model): void
    {
        if (empty($model)) {
            return ;
        }
        $category = property_exists($model , 'category') ? $model->category : CategoryModel::find($model->category_id);
        $category = CategoryHandler::handle($category);
        $model->category = $category;
    }

    public static function tags($model): void
    {
        if (empty($model)) {
            return ;
        }
        $tags = RelationTagModel::getByRelationTypeAndRelationId('video_project' , $model->id);
        $model->tags = $tags;
    }

    public static function videos($model): void
    {
        if (empty($model)) {
            return ;
        }
        $videos = property_exists($model , 'videos') ? $model->videos : VideoModel::getByVideoProjectId($model->id);
        $videos = VideoHandler::handleAll($videos);
        $model->videos = $videos;
    }
}
