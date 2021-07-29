<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\CollectionModel;
use App\Customize\api\web\model\PraiseModel;
use App\Customize\api\web\model\RelationTagModel;
use App\Customize\api\web\model\UserModel;
use App\Customize\api\web\model\UserVideoPlayRecordModel;
use App\Customize\api\web\model\UserVideoProjectPlayRecordModel;
use App\Customize\api\web\model\VideoSubtitleModel;
use App\Customize\api\web\model\VideoSrcModel;
use App\Customize\api\web\model\VideoModel;
use App\Customize\api\web\repository\FileRepository;
use App\Customize\api\web\model\Model;
use stdClass;
use function api\web\user;
use function core\convert_object;
use function core\format_time;
use function core\get_time_diff;

class VideoHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        $model->__duration__    = empty($model->duration) ? 0 : format_time($model->duration , 'HH:II:SS');
        $model->__thumb__       = empty($model->thumb) ? $model->thumb_for_program : $model->thumb;

        $model->__name__ = $model->type === 'pro' ?
            (empty($model->name) ? sprintf("%'04s" , $model->index) : $model->name)
            :
            $model->name;

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
            $model->is_collected = CollectionModel::findByModuleIdAndUserIdAndRelationTypeAndRelationId($model->module_id , user()->id , 'video' , $model->id) ? 1 : 0;
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
            $model->is_praised = PraiseModel::findByModuleIdAndUserIdAndRelationTypeAndRelationId($model->module_id , user()->id , 'video' , $model->id) ? 1 : 0;
        }
    }

    // 附加：标签
    public static function tags($model): void
    {
        if (empty($model)) {
            return ;
        }
        $tags = property_exists($model , 'tags') ? $model->tags : RelationTagModel::getByRelationTypeAndRelationId('video' , $model->id);
        $tags = RelationTagHandler::handleAll($tags);
        $model->tags = $tags;
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

    // 附加：用户播放记录
    public static function userPlayRecord($model): void
    {
        if (empty($model)) {
            return ;
        }
        $user = user();
        $user_play_record = null;
        if (!empty($user)) {
            $user_play_record = property_exists($model , 'user_play_record') ? $model->user_play_record : UserVideoPlayRecordModel::findByModuleIdAndUserIdAndVideoId($model->module_id , $user->id , $model->id);
            $user_play_record = UserVideoPlayRecordHandler::handle($user_play_record);
        }
        $model->user_play_record = $user_play_record;
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

}
