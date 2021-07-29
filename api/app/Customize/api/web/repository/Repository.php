<?php


namespace App\Customize\api\web\repository;


use App\Customize\api\web\handler\HistoryHandler;
use App\Customize\api\web\handler\PraiseHandler;
use App\Customize\api\web\handler\VideoHandler;
use App\Customize\api\web\model\ImageModel;
use App\Customize\api\web\model\ImageProjectModel;
use App\Customize\api\web\model\UserModel;
use App\Customize\api\web\model\UserVideoPlayRecordModel;
use App\Customize\api\web\model\UserVideoProjectPlayRecordModel;
use App\Customize\api\web\model\VideoModel;
use App\Customize\api\web\model\VideoProjectModel;
use function api\web\user;
use function core\convert_object;

class Repository
{
    public static function groupViaDateByModuleAndTypeAndCollectionSupportHistoryAndPraise($module , string $type , array $collection): array
    {
        $user = user();
        // 对时间进行分组
        $date = date('Y-m-d');
        $yesterday = date('Y-m-d' , strtotime('yesterday'));
        $groups = [];
        $findIndex = function($name) use(&$groups): int
        {
            foreach ($groups as $k => $v)
            {
                if ($v['name'] === $name) {
                    return $k;
                }
            }
            return -1;
        };
        // 采预加载的方式进行改造
        $image_project_ids = [];
        $image_ids = [];
        $video_project_ids = [];
        $video_ids = [];
        $user_ids = [];
        $video_ids_in_video_project = [];

        array_walk($collection , function($v) use(&$image_project_ids , &$image_ids , &$video_project_ids , &$video_ids){
            switch ($v->relation_type)
            {
                case 'image_project':
                    $image_project_ids[] = $v->relation_id;
                    break;
                case 'image':
                    $image_ids[] = $v->relation_id;
                    break;
                case 'video_project':
                    $video_project_ids[] = $v->relation_id;
                    break;
                case 'video':
                    $video_ids[] = $v->relation_id;
                    break;
            }
        });
        $image_projects = ImageProjectModel::getByIds($image_project_ids);
        $images         = ImageModel::getByIds($image_ids);
        $video_projects = VideoProjectModel::getByIds($video_project_ids);
        $videos         = VideoModel::getByIds($video_ids);
        $videos         = VideoHandler::handleAll($videos);
        // 视频专题 - 播放记录
        $user_video_project_play_records = UserVideoProjectPlayRecordModel::getByModuleIdAndUserIdAndVideoProjectIds($module->id , $user->id , $video_project_ids);
        $user_video_play_records         = UserVideoPlayRecordModel::getByModuleIdAndUserIdAndVideoIds($module->id , $user->id , $video_ids);

        foreach ($collection as $v)
        {
            $v->relation = null;
            foreach ($image_projects as $image_project)
            {
                if ($v->relation_type !== 'image_project') {
                    continue ;
                }
                if ($v->relation_id !== $image_project->id) {
                    continue ;
                }
                $user_ids[] = $image_project->user_id;
                $v->relation = $image_project;
                break;
            }
            foreach ($images as $image)
            {
                if ($v->relation_type !== 'image') {
                    continue ;
                }
                if ($v->relation_id !== $image->id) {
                    continue ;
                }
                $user_ids[] = $image->user_id;
                $v->relation = $image;
                break;
            }
            foreach ($video_projects as $video_project)
            {
                if ($v->relation_type !== 'video_project') {
                    continue ;
                }
                if ($v->relation_id !== $video_project->id) {
                    continue ;
                }
                $user_ids[] = $video_project->user_id;
                $v->relation = $video_project;
                break;
            }
            foreach ($videos as $video)
            {
                if ($v->relation_type !== 'video') {
                    continue ;
                }
                if ($v->relation_id !== $video->id) {
                    continue ;
                }
                $user_ids[] = $video->user_id;
                $v->relation = $video;
                break;
            }
            if ($v->relation_type === 'video' && !empty($v->relation)) {
                foreach ($user_video_play_records as $user_video_play_record)
                {
                    if ($v->relation->id === $user_video_play_record->video_id) {
                        $v->relation->user_play_record = $user_video_play_record;
                        break;
                    }
                }
            }
            if ($v->relation_type === 'video_project' && !empty($v->relation)) {
                foreach ($user_video_project_play_records as $user_video_project_play_record)
                {
                    if ($v->relation->id === $user_video_project_play_record->video_project_id) {
                        $v->relation->user_play_record = $user_video_project_play_record;
                        $video_ids_in_video_project[] = $user_video_project_play_record->video_id;
                        break;
                    }
                }
            }
            if ($type === 'history') {
                HistoryHandler::relation($v);
            } else if ($type === 'praise') {
                PraiseHandler::relation($v);
            } else {
                // todo 预留
            }
        }
        $users = UserModel::getByIds($user_ids);
        $videos_in_video_projects = VideoModel::getByIds($video_ids_in_video_project);
        $videos_in_video_projects = VideoHandler::handleAll($videos_in_video_projects);
        foreach ($collection as $v)
        {
            if (!empty($v->relation)) {
                foreach ($users as $user)
                {
                    if ($user->id === $v->relation->user_id) {
                        $v->relation->user = $user;
                        break;
                    }
                }
            }
            if ($v->relation_type === 'video_project' && !empty($v->relation) && !empty($v->relation->user_play_record)) {
                foreach ($videos_in_video_projects as $video)
                {
                    if ($video->id === $v->relation->user_play_record->video_id) {
                        $v->relation->user_play_record->video = $video;
                        break;
                    }
                }
            }
            if ($v->relation_type === 'video' && !empty($v->relation) && !empty($v->relation->user_play_record)) {
                $relation = convert_object($v->relation);
                unset($relation->user_play_record);
                unset($relation->user);
                $v->relation->user_play_record->video = $relation;
            }
            switch ($v->date)
            {
                case $date:
                    $name = '今天';
                    break;
                case $yesterday:
                    $name = '昨天';
                    break;
                default:
                    $name = $v->date;
            }
            $index = $findIndex($name);
            if ($index < 0) {
                $groups[] = [
                    'name' => $name ,
                    'data' => [] ,
                ];
                $index = count($groups) - 1;
            }
            $groups[$index]['data'][] = $v;
        }
        return $groups;
    }

    // 预加载处理
    public static function withRelationUsePreloadByModuleAndCollectionSupportCollection($module , array $collection): void
    {
        $user = user();
        $image_project_ids = [];
        $image_ids = [];
        $video_project_ids = [];
        $video_ids = [];
        $user_ids = [];
        $video_ids_in_video_project = [];
        foreach ($collection as $v)
        {
            switch ($v->relation_type)
            {
                case 'image_project':
                    $image_project_ids[] = $v->relation_id;
                    break;
                case 'image':
                    $image_ids[] = $v->relation_id;
                    break;
                case 'video_project':
                    $video_project_ids[] = $v->relation_id;
                    break;
                case 'video':
                    $video_ids[] = $v->relation_id;
                    break;
            }
        }
        $image_projects = ImageProjectModel::getByIds($image_project_ids);
        $images         = ImageModel::getByIds($image_ids);
        $video_projects = VideoProjectModel::getByIds($video_project_ids);
        $videos         = VideoModel::getByIds($video_ids);
        $videos         = VideoHandler::handleAll($videos);
        // 视频专题 - 播放记录
        $user_video_project_play_records = UserVideoProjectPlayRecordModel::getByModuleIdAndUserIdAndVideoProjectIds($module->id , $user->id , $video_project_ids);
        $user_video_play_records         = UserVideoPlayRecordModel::getByModuleIdAndUserIdAndVideoIds($module->id , $user->id , $video_ids);

        foreach ($collection as $v)
        {
            $v->relation = null;
            foreach ($image_projects as $image_project)
            {
                if ($v->relation_type !== 'image_project') {
                    continue ;
                }
                if ($v->relation_id !== $image_project->id) {
                    continue ;
                }
                $user_ids[] = $image_project->user_id;
                $v->relation = $image_project;
                break;
            }
            foreach ($images as $image)
            {
                if ($v->relation_type !== 'image') {
                    continue ;
                }
                if ($v->relation_id !== $image->id) {
                    continue ;
                }
                $user_ids[] = $image->user_id;
                $v->relation = $image;
                break;
            }
            foreach ($video_projects as $video_project)
            {
                if ($v->relation_type !== 'video_project') {
                    continue ;
                }
                if ($v->relation_id !== $video_project->id) {
                    continue ;
                }
                $user_ids[] = $video_project->user_id;
                $v->relation = $video_project;
                break;
            }
            foreach ($videos as $video)
            {
                if ($v->relation_type !== 'video') {
                    continue ;
                }
                if ($v->relation_id !== $video->id) {
                    continue ;
                }
                $user_ids[] = $video->user_id;
                $v->relation = $video;
                break;
            }
            if ($v->relation_type === 'video' && !empty($v->relation)) {
                foreach ($user_video_play_records as $user_video_play_record)
                {
                    if ($v->relation->id === $user_video_play_record->video_id) {
                        $v->relation->user_play_record = $user_video_play_record;
                        break;
                    }
                }
            }
            if ($v->relation_type === 'video_project' && !empty($v->relation)) {
                foreach ($user_video_project_play_records as $user_video_project_play_record)
                {
                    if ($v->relation->id === $user_video_project_play_record->video_project_id) {
                        $v->relation->user_play_record = $user_video_project_play_record;
                        $video_ids_in_video_project[] = $user_video_project_play_record->video_id;
                        break;
                    }
                }
            }
        }
        $users = UserModel::getByIds($user_ids);
        $videos_in_video_projects = VideoModel::getByIds($video_ids_in_video_project);
        $videos_in_video_projects = VideoHandler::handleAll($videos_in_video_projects);
        foreach ($collection as $v)
        {
            if (!empty($v->relation)) {
                foreach ($users as $user)
                {
                    if ($user->id === $v->relation->user_id) {
                        $v->relation->user = $user;
                        break;
                    }
                }
            }
            if ($v->relation_type === 'video_project' && !empty($v->relation) && !empty($v->relation->user_play_record)) {
                foreach ($videos_in_video_projects as $video)
                {
                    if ($video->id === $v->relation->user_play_record->video_id) {
                        $v->relation->user_play_record->video = $video;
                        break;
                    }
                }
            }
            if ($v->relation_type === 'video' && !empty($v->relation) && !empty($v->relation->user_play_record)) {
                $relation = convert_object($v->relation);
                unset($relation->user_play_record);
                unset($relation->user);
                $v->relation->user_play_record->video = $relation;
            }

        }
    }
}
