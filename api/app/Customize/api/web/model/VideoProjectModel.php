<?php


namespace App\Customize\api\web\model;


use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VideoProjectModel extends Model
{
    protected $table = 'xq_video_project';

    public function user()
    {
        return $this->belongsTo(UserModel::class , 'user_id' , 'id');
    }

    public function tags()
    {
        return $this->hasMany(RelationTagModel::class , 'relation_id' , 'id');
    }

    public static function getNewestByRelationAndFilterAndSize(array $relation = [] , array $filter = [] , int $size = 0): Collection
    {
        $filter['module_id'] = $filter['module_id'] ?? '';
        $where = [];
        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with[$v] = function($query){
                    $query->where('relation_type' , 'video_project');
                };
                continue ;
            }
            $with[] = $v;
        }
        if ($filter['module_id'] !== '') {
            $where[] = ['module_id' , '=' , $filter['module_id']];
        }

        return self::with($with)
            ->where($where)
            ->orderBy('created_at' , 'desc')
            ->orderBy('id' , 'asc')
            ->limit($size)
            ->get();
    }

    public static function getHotByRelationAndFilterAndSize(array $relation = [] , array $filter = [] , int $size = 0): Collection
    {
        $filter['module_id'] = $filter['module_id'] ?? '';

        $where = [];

        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with[$v] = function($query){
                    $query->where('relation_type' , 'video_project');
                };
                continue ;
            }
            $with[] = $v;
        }

        if ($filter['module_id'] !== '') {
            $where[] = ['module_id' , '=' , $filter['module_id']];
        }

        return self::with($with)
            ->where($where)
            ->orderBy('praise_count' , 'desc')
            ->orderBy('play_count' , 'desc')
            ->orderBy('view_count' , 'desc')
            ->orderBy('against_count' , 'asc')
            ->orderBy('created_at' , 'desc')
            ->orderBy('id' , 'asc')
            ->limit($size)
            ->get();
    }

    public static function getHotWithPagerByRelationAndFilterAndSize(array $relation = [] , array $filter = [] , int $size = 0): Paginator
    {
        $filter['module_id'] = $filter['module_id'] ?? '';

        $where = [];
        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with[$v] = function($query){
                    $query->where('relation_type' , 'video_project');
                };
                continue ;
            }
            $with[] = $v;
        }

        if ($filter['module_id'] !== '') {
            $where[] = ['module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['type' , '=' , $filter['type']];
        }
        return self::with($with)
            ->where($where)
            ->orderBy('praise_count' , 'desc')
            ->orderBy('play_count' , 'desc')
            ->orderBy('view_count' , 'desc')
            ->orderBy('against_count' , 'asc')
            ->orderBy('created_at' , 'desc')
            ->orderBy('id' , 'asc')
            ->paginate($size);
    }


    public static function getByRelationAndTagIdAndFilterAndSize(array $relation , int $tag_id , array $filter = [] , int $size = 0): Collection
    {
        $filter['module_id'] = $filter['module_id'] ?? '';

        $where = [
            ['vs.status' , '=' , 1] ,
        ];

        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with[$v] = function($query){
                    $query->where('relation_type' , 'video_project');
                };
                continue ;
            }
            $with[] = $v;
        }

        if ($filter['module_id'] !== '') {
            $where[] = ['vs.module_id' , '=' , $filter['module_id']];
        }

        return self::with($with)
            ->from('xq_video_project as vs')
            ->select('vs.*')
            ->where($where)
            ->whereExists(function($query) use($tag_id){
                $query->from('xq_relation_tag')
                    ->where([
                        ['tag_id' , '=' , $tag_id] ,
                        ['relation_type' , '=' , 'video_project'] ,
                    ])
                    ->whereRaw('vs.id = relation_id');
            })
            ->orderBy('vs.created_at' , 'desc')
            ->orderBy('vs.id' , 'asc')
            ->limit($size)
            ->get();
    }

    // 标签对应的图片专题-非严格模式匹配
    public static function getByTagIdsAndFilterAndSize(array $tag_ids = [] , array $filter = [] , int $size = 0): Paginator
    {
        $filter['module_id'] = $filter['module_id'] ?? '';

        $where = [];

        if ($filter['module_id'] !== '') {
            $where[] = ['vs.module_id' , '=' , $filter['module_id']];
        }

        return self::from('xq_video_project as vs')
            ->where($where)
            ->whereExists(function($query) use($tag_ids){
                $query->from('xq_relation_tag')
                    ->where([
                        ['relation_type' , '=' , 'video_project'] ,
                    ])
                    ->whereIn('tag_id' , $tag_ids)
                    ->whereRaw('vs.id = relation_id');
            })
            ->orderBy('vs.created_at' , 'desc')
            ->orderBy('vs.id' , 'asc')
            ->paginate($size);
    }

    // 标签对应的图片专题-严格模式匹配
    public static function getInStrictByTagIdsAndFilterAndSize(array $tag_ids = [] , array $filter = [] , int $size = 0): Paginator
    {
        $filter['module_id'] = $filter['module_id'] ?? '';

        $where = [];

        if ($filter['module_id'] !== '') {
            $where[] = ['vs.module_id' , '=' , $filter['module_id']];
        }

        return self::from('xq_video_project as vs')
            ->where($where)
            ->whereExists(function($query) use($tag_ids){
                $query->select('id')
                    ->selectRaw('count(id) as total')
                    ->from('xq_relation_tag')
                    ->where([
                        ['relation_type' , '=' , 'video_project'] ,
                    ])
                    ->whereIn('tag_id' , $tag_ids)
                    ->whereRaw('vs.id = relation_id')
                    ->groupBy('relation_id')
                    ->having('total' , '=' , count($tag_ids));
            })
            ->orderBy('vs.created_at' , 'desc')
            ->orderBy('vs.id' , 'asc')
            ->paginate($size);
    }

    public static function getNewestWithPagerByFilterAndSize(array $filter = [] , int $size = 0): Paginator
    {
        $filter['module_id'] = $filter['module_id'] ?? '';

        $where = [];

        if ($filter['module_id'] !== '') {
            $where[] = ['module_id' , '=' , $filter['module_id']];
        }

        return self::where($where)
            ->orderBy('created_at' , 'desc')
            ->orderBy('id' , 'asc')
            ->paginate($size);
    }

    public static function getWithPagerInStrictByRelationAndFilterAndOrderAndSize(array $relation = [] , array $filter = [] , $order = null , int $size = 20)
    {
        $filter['value']        = $filter['value'] ?? '';
        $filter['module_id']    = $filter['module_id'] ?? '';
        $filter['tag_ids']      = $filter['tag_ids'] ?? [];
        $filter['video_series_ids']      = $filter['video_series_ids'] ?? [];
        $filter['video_company_ids']      = $filter['video_company_ids'] ?? [];
        $filter['category_ids']      = $filter['category_ids'] ?? [];

        $order = $order ?? ['field' => 'created_at' , 'value' => 'desc'];

        $where = [];

        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with[$v] = function($query){
                    $query->where('relation_type' , 'video_project');
                };
                continue ;
            }
            $with[] = $v;
        }

        if ($filter['module_id'] !== '') {
            $where[] = ['vp.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['value'] !== '') {
            $where[] = ['vp.name' , 'like' , "%{$filter['value']}%"];
        }

        $query = self::with($with)
            ->from('xq_video_project as vp')
            ->where($where);

        if (!empty($filter['video_series_ids'])) {
            $query->whereIn('vp.video_series_id' , $filter['video_series_ids']);
        }

        if (!empty($filter['video_company_ids'])) {
            $query->whereIn('vp.video_company_id' , $filter['video_company_ids']);
        }

        if (!empty($filter['category_ids'])) {
            $query->whereIn('vp.category_id' , $filter['category_ids']);
        }

        if (!empty($filter['tag_ids'])) {
            $query->whereExists(function($query) use($filter){
                $query->select('id')
                    ->selectRaw('count(id) as total')
                    ->from('xq_relation_tag')
                    ->where([
                        ['relation_type' , '=' , 'video_project'] ,
                    ])
                    ->whereIn('tag_id' , $filter['tag_ids'])
                    ->groupBy('relation_id')
                    ->having('total' , '=' , count($filter['tag_ids']))
                    ->whereRaw('relation_id = vp.id');
            });
        }

        return $query->orderBy("vp.{$order['field']}" , $order['value'])
            ->orderBy('vp.id' , 'desc')
            ->paginate($size);
    }

    public static function getWithPagerInLooseByRelationAndFilterAndOrderAndSize(array $relation = [] , array $filter = [] , $order = null , int $size = 20)
    {
        $filter['value']        = $filter['value'] ?? '';
        $filter['module_id']    = $filter['module_id'] ?? '';
        $filter['tag_ids']      = $filter['tag_ids'] ?? [];
        $filter['video_series_ids']      = $filter['video_series_ids'] ?? [];
        $filter['video_company_ids']      = $filter['video_company_ids'] ?? [];
        $filter['category_ids']      = $filter['category_ids'] ?? [];

        $order = $order ?? ['field' => 'created_at' , 'value' => 'desc'];

        $where = [];


        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with[$v] = function($query){
                    $query->where('relation_type' , 'video_project');
                };
                continue ;
            }
            $with[] = $v;
        }


        if ($filter['module_id'] !== '') {
            $where[] = ['vp.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['value'] !== '') {
            $where[] = ['vp.name' , 'like' , "%{$filter['value']}%"];
        }

        $query = self::with($with)
            ->from('xq_video_project as vp')
            ->where($where);

        if (!empty($filter['video_series_ids'])) {
            $query->whereIn('video_series_id' , $filter['video_series_ids']);
        }

        if (!empty($filter['video_company_ids'])) {
            $query->whereIn('video_company_id' , $filter['video_company_ids']);
        }

        if (!empty($filter['category_ids'])) {
            $query->whereIn('category_id' , $filter['category_ids']);
        }

        if (!empty($filter['tag_ids'])) {
            $query->whereExists(function($query) use($filter){
                $query->select('id')
                    ->from('xq_relation_tag')
                    ->where([
                        ['relation_type' , '=' , 'video_project'] ,
                    ])
                    ->whereRaw('relation_id = vp.id')
                    ->whereIn('tag_id' , $filter['tag_ids']);
            });
        }

        return $query->orderBy("vp.{$order['field']}" , $order['value'])
            ->orderBy('vp.id' , 'desc')
            ->paginate($size);
    }

    public static function countHandle(int $id , string $field , string $mode = '' , int $step = 1): int
    {
        $mode_range = ['increment' , 'decrement'];
        if (!in_array($mode , $mode_range)) {
            throw new Exception('不支持的操作模式，当前支持的模式有：' . implode(',' , $mode_range));
        }
        return self::where('id' , $id)->$mode($field , $step);
    }

    public static function recommendExcludeSelfByFilterAndSize(int $self_id , array $filter = [] , int $size = 20): Collection
    {
        $filter['module_id']    = $filter['module_id'] ?? '';
        $filter['category_id']  = $filter['category_id'] ?? '';

        $where = [
            ['id' , '!=' , $self_id] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['module_id' , '=' , $filter['module_id']];
        }

        if ($filter['category_id'] !== '') {
            $where[] = ['category_id' , '=' , $filter['category_id']];
        }

        return self::where($where)
            ->orderBy('praise_count' , 'desc')
            ->orderBy('play_count' , 'desc')
            ->orderBy('view_count' , 'desc')
            ->orderBy('against_count' , 'asc')
            ->orderBy('created_at' , 'desc')
            ->limit($size)
            ->get();
    }

    public static function getByVideoSeriesIdAndExcludeVideoProjectId(int $video_series_id , int $exclude_video_project_id): Collection
    {
        return self::where([
                ['id' , '!=' , $exclude_video_project_id] ,
                ['video_series_id' , '=' , $video_series_id] ,
            ])
            ->get();
    }


}
