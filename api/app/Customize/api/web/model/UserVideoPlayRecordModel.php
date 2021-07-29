<?php


namespace App\Customize\api\web\model;


use Illuminate\Database\Eloquent\Collection;

class UserVideoPlayRecordModel extends Model
{
    protected $table = 'xq_user_video_play_record';

    public static function findByModuleIdAndUserIdAndVideoId(int $module_id , int $user_id , int $video_id): ?UserVideoPlayRecordModel
    {
        return self::where([
            ['module_id' , '=' , $module_id] ,
            ['user_id' , '=' , $user_id] ,
            ['video_id' , '=' , $video_id] ,
        ])->first();
    }

    public static function getByModuleIdAndUserIdAndVideoIds(int $module_id , int $user_id , array $video_ids)
    {
        return self::where([
            ['module_id' , '=' , $module_id] ,
            ['user_id' , '=' , $user_id] ,
        ])
            ->whereIn('video_id' , $video_ids)
            ->get();
    }
}
