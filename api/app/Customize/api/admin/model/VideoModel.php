<?php


namespace App\Customize\api\admin\model;


use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VideoModel extends Model
{
    protected $table = 'xq_video';

    public function user()
    {
        return $this->belongsTo(UserModel::class , 'user_id' , 'id');
    }

    public function module()
    {
        return $this->belongsTo(ModuleModel::class , 'module_id' , 'id');
    }

    public function category()
    {
        return $this->belongsTo(CategoryModel::class , 'category_id' , 'id');
    }

    public function videoProject()
    {
        return $this->belongsTo(VideoProjectModel::class , 'video_project_id' , 'id');
    }

    public function videos()
    {
        return $this->hasMany(VideoSrcModel::class , 'video_id' , 'id');
    }

    public function videoSubtitles()
    {
        return $this->hasMany(VideoSubtitleModel::class , 'video_id' , 'id');
    }

    public static function index(array $relation = [] , array $filter = [] , array $order = [] , int $size = 20): Paginator
    {
        $filter['id']           = $filter['id'] ?? '';
        $filter['name']         = $filter['name'] ?? '';
        $filter['user_id']      = $filter['user_id'] ?? '';
        $filter['module_id']    = $filter['module_id'] ?? '';
        $filter['category_id']          = $filter['category_id'] ?? '';
        $filter['video_project_id']     = $filter['video_project_id'] ?? '';
        $filter['type']         = $filter['type'] ?? '';
        $filter['status']       = $filter['status'] ?? '';
        $filter['file_process_status']       = $filter['file_process_status'] ?? '';
        $filter['video_process_status']       = $filter['video_process_status'] ?? '';

        $order['field'] = $order['field'] ?? 'id';
        $order['value'] = $order['value'] ?? 'desc';

        $where = [];

        if ($filter['id'] !== '') {
            $where[] = ['id' , '=' , $filter['id']];
        }

        if ($filter['name'] !== '') {
            $where[] = ['name' , 'like' , "%{$filter['name']}%"];
        }

        if ($filter['user_id'] !== '') {
            $where[] = ['user_id' , '=' , $filter['user_id']];
        }

        if ($filter['module_id'] !== '') {
            $where[] = ['module_id' , '=' , $filter['module_id']];
        }

        if ($filter['category_id'] !== '') {
            $where[] = ['category_id' , '=' , $filter['category_id']];
        }

        if ($filter['video_project_id'] !== '') {
            $where[] = ['video_project_id' , '=' , $filter['video_project_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['type' , '=' , $filter['type']];
        }

        if ($filter['status'] !== '') {
            $where[] = ['status' , '=' , $filter['status']];
        }

        if ($filter['file_process_status'] !== '') {
            $where[] = ['file_process_status' , '=' , $filter['file_process_status']];
        }

        if ($filter['video_process_status'] !== '') {
            $where[] = ['video_process_status' , '=' , $filter['video_process_status']];
        }

        return self::with($relation)
            ->where($where)
            ->orderBy($order['field'] , $order['value'])
            ->paginate($size);
    }

    public static function countByDateAndProcessStatus(string $date , int $process_status): int
    {
        return self::whereRaw('date_format(created_at , "%Y-%m-%d") = ?' , $date)
            ->where('video_process_status' , $process_status)
            ->count();
    }

    public static function countByProcessStatus(int $process_status): int
    {
        return self::where('video_process_status' , $process_status)
            ->count();
    }

    // 检查剧集是否已经存在
    public static function findByVideoProjectIdAndIndex(int $video_project_id , int $index): ?VideoModel
    {
        return self::where([
            ['type' , '=' , 'pro'] ,
            ['video_project_id' , '=' , $video_project_id] ,
            ['index' , '=' , $index] ,
        ])->first();
    }

    public static function findByExcludeIdAndVideoProjectIdAndIndex(int $exclude_id , int $video_project_id , int $index): ?VideoModel
    {
        return self::where([
            ['id' , '!=' , $exclude_id] ,
            ['type' , '=' , 'pro'] ,
            ['video_project_id' , '=' , $video_project_id] ,
            ['index' , '=' , $index] ,
        ])->first();
    }

    public static function getByVideoProjectId(int $video_project_id): Collection
    {
        return self::where([
            ['type' , '=' , 'pro'] ,
            ['video_project_id' , '=' , $video_project_id] ,
        ])->get();
    }

    public static function findByModuleIdAndName(int $module_id , string $name): ?VideoModel
    {
        return self::where([
            ['module_id' , '=' , $module_id] ,
            ['name' , '=' , $name] ,
        ])->first();
    }

    public static function findByModuleIdAndExcludeIdAndName(int $module_id , int $exclude_id , string $name): ?VideoModel
    {
        return self::where([
            ['id' , '!=' , $exclude_id] ,
            ['module_id' , '=' , $module_id] ,
            ['name' , '=' , $name] ,
        ])->first();
    }
}
