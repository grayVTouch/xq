<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\UserModel;
use App\Customize\api\web\model\UserVideoProjectPlayRecordModel;
use App\Customize\api\web\model\VideoSeriesModel;
use App\Customize\api\web\model\VideoCompanyModel;
use App\Customize\api\web\model\CollectionModel;
use App\Customize\api\web\model\PraiseModel;
use App\Customize\api\web\model\RelationTagModel;
use App\Customize\api\web\model\CategoryModel;
use App\Customize\api\web\model\VideoModel;
use App\Customize\api\web\model\VideoProjectModel;
use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\repository\FileRepository;
use App\Customize\api\web\model\Model;
use stdClass;
use function api\web\get_config_key_mapping_value;
use function api\web\get_value;
use function api\web\user;
use function core\convert_object;
use function core\get_time_diff;

class VideoProjectHandler extends Handler
{
    public static function handle(?Model $model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        $model->__status__ = get_config_key_mapping_value('business.status_for_video_project' , $model->status);

        $model->format_time = get_time_diff($model->created_at);

        return $model;
    }

    // 附加：是否收藏
    public static function isCollected($model): void
    {
        if (empty($model)) {
            return ;
        }
        if (empty(user())) {
            $model->is_collected = 0;
        } else {
            $model->is_collected = CollectionModel::findByModuleIdAndUserIdAndRelationTypeAndRelationId($model->module_id , user()->id , 'video_project' , $model->id) ? 1 : 0;
        }
    }

    // 附加：是否点赞
    public static function isPraised($model): void
    {
        if (empty($model)) {
            return ;
        }
        if (empty(user())) {
            $model->is_praised = 0;
        } else {
            $model->is_praised = PraiseModel::findByModuleIdAndUserIdAndRelationTypeAndRelationId($model->module_id , user()->id , 'video_project' , $model->id) ? 1 : 0;
        }
    }

    // 附加：用户播放记录
    public static function userPlayRecord($model): void
    {
        if (empty($model)) {
            return ;
        }
        $user = user();
        $user_play_record = null;
        if (!empty($user)) {
            $user_play_record = UserVideoProjectPlayRecordModel::findByModuleIdAndUserIdAndVideoProjectId($model->module_id , $user->id , $model->id);
        }
        $model->user_play_record = $user_play_record;
    }


    public static function videos($model): void
    {
        if (empty($model)) {
            return ;
        }
        $videos = VideoModel::getByVideoProjectId($model->id);
        $videos = VideoHandler::handleAll($videos);

        $model->videos = $videos;
    }

     public static function user($model): void
    {
        if (empty($model)) {
            return ;
        }
        $user = UserModel::find($model->user_id);
        $user = UserHandler::handle($user);

        $model->user = $user;
    }

    // 附加：标签
    public static function tags($model): void
    {
        if (empty($model)) {
            return ;
        }
        $tags = RelationTagModel::getByRelationTypeAndRelationId('video_project' , $model->id);
        $tags = RelationTagHandler::handleAll($tags);
        $model->tags = $tags;
    }

    public static function videoCompany($model): void
    {
        if (empty($model)) {
            return ;
        }
        $video_company = VideoCompanyModel::find($model->video_company_id);
        $video_company = VideoCompanyHandler::handle($video_company);

        $model->video_company = $video_company;
    }

    public static function videoSeries($model): void
    {
        if (empty($model)) {
            return ;
        }
        $video_series = VideoSeriesModel::find($model->video_series_id);
        $video_series = VideoSeriesHandler::handle($video_series);

        $model->video_series = $video_series;
    }

    public static function category($model): void
    {
        if (empty($model)) {
            return ;
        }
        $category = CategoryModel::find($model->category_id);
        $category = CategoryHandler::handle($category);

        $model->category = $category;
    }

}
