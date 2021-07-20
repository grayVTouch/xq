<?php


namespace App\Customize\api\web\model;


use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RelationTagModel extends Model
{
    protected $table = 'xq_relation_tag';

    public static function getByRelationTypeAndRelationId(string $relation_type , int $relation_id): Collection
    {
        return self::where([
                    ['relation_type' , '=' , $relation_type] ,
                    ['relation_id' , '=' , $relation_id] ,
                ])
                ->get();
    }

    public static function delByRelationTypeAndRelationId(string $relation_type , int $relation_id): int
    {
        return self::where([
                    ['relation_type' , '=' , $relation_type] ,
                    ['relation_id' , '=' , $relation_id] ,
                ])
                ->delete();
    }

    public static function delByRelationTypeAndRelationIdAndTagId(string $relation_type , int $relation_id , int $tag_id): int
    {
        return self::where([
            ['relation_type' , '=' , $relation_type] ,
            ['relation_id' , '=' , $relation_id] ,
            ['tag_id' , '=' , $tag_id] ,
        ])->delete();
    }

    // 热门标签-返回给定数量
    public static function hotTagsByRelationTypeAndFilterAndSize(string $relation_type , array $filter = [] , int $size = 20): Collection
    {
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['type']      = $filter['type'] ?? '';

        $where = [
            ['rt.relation_type' , '=' , $relation_type] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['rt.module_id' , '=' , $filter['module_id']];
        }

        return self::from('xq_relation_tag as rt')
            ->select('rt.*' , DB::raw('count(rt.id) as total'))
            ->where($where)
            ->whereExists(function($query) use($relation_type , $filter){
                if ($relation_type === 'image_project') {
                    $query->where('type' , $filter['type']);
                }
            })
            ->groupBy('rt.tag_id')
            ->orderBy('total' , 'desc')
            ->orderBy('rt.id' , 'asc')
            ->limit($size)
            ->get();
    }

    // 热门标签-返回给定数量
    public static function hotTagsInImageProjectByFilterAndSize(array $filter = [] , int $size = 20): Collection
    {
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['type']      = $filter['type'] ?? '';

        $where = [
            ['rt.relation_type' , '=' , 'image_project'] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['rt.module_id' , '=' , $filter['module_id']];
        }

        return self::from('xq_relation_tag as rt')
            ->select('rt.*' , DB::raw('count(rt.id) as total'))
            ->where($where)
            ->whereExists(function($query) use($filter){
                if ($filter['type'] === '') {
                    return ;
                }
                $query->from('xq_image_project')
                    ->where('type' , $filter['type'])
                    ->whereRaw('rt.relation_id = id');
            })
            ->groupBy('rt.tag_id')
            ->orderBy('total' , 'desc')
            ->orderBy('rt.id' , 'asc')
            ->limit($size)
            ->get();
    }

    public static function hotTagsInVideoSubjectByFilterAndSize(array $filter = [] , int $size = 20): Collection
    {
        $filter['module_id'] = $filter['module_id'] ?? '';

        $where = [
            ['relation_type' , '=' , 'video_project'] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['module_id' , '=' , $filter['module_id']];
        }

        return self::select('*' , DB::raw('count(id) as total'))
            ->where($where)
            ->groupBy('tag_id')
            ->orderBy('total' , 'desc')
            ->orderBy('id' , 'asc')
            ->limit($size)
            ->get();
    }

    // 热门标签-分页
    public static function hotTagsWithPagerByValueAndRelationTypeAndFilterAndSize(string $value , string $relation_type , array $filter = [] , int $size = 20): Paginator
    {
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['type']      = $filter['type'] ?? '';

        $where = [
            ['rt.relation_type' , '=' , $relation_type] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['rt.module_id' , '=' , $filter['module_id']];
        }
        $value = strtolower($value);
        return self::select('*' , DB::raw('count(id) as total'))
            ->where($where)
            // like '%' and name like '%'
            ->whereRaw("lower(name) like ?" , "%{$value}%")
            ->groupBy('tag_id')
            ->orderBy('total' , 'desc')
            ->orderBy('id' , 'asc')
            ->paginate($size);
    }

    // 热门标签-图片专题-分页
    public static function hotTagsWithPagerInImageProjectByValueAndFilterAndSize(string $value , array $filter = [] , int $size = 20): Paginator
    {
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['type']      = $filter['type'] ?? '';

        $where = [
            ['rt.relation_type' , '=' , 'image_project'] ,
        ];
        if ($filter['module_id'] !== '') {
            $where[] = ['rt.module_id' , '=' , $filter['module_id']];
        }
        if ($filter['value'] !== '') {
            $where[] = ['rt.name' , 'like' , "%{$value}%"];
        }
        $query = self::from('xq_relation_tag as rt')
            ->select('rt.*' , DB::raw('count(rt.id) as total'))
            ->where($where);
        if ($filter['type'] !== '') {
            $query->whereExists(function($query) use($filter){
                $query->from('xq_image_project')
                    ->where('type' , $filter['type'])
                    ->whereRaw('rt.relation_id = id');
            });
        }
        return $query->groupBy('rt.tag_id')
            ->orderBy('total' , 'desc')
            ->orderBy('rt.id' , 'asc')
            ->paginate($size);
    }

    // 热门标签-视频专题-分页
    public static function hotTagsWithPagerInVideoProjectByValueAndFilterAndSize(string $value , array $filter = [] , int $size = 20): Paginator
    {
        $filter['module_id'] = $filter['module_id'] ?? '';

        $where = [
            ['rt.relation_type' , '=' , 'video_project'] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['rt.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['value'] !== '') {
            $where[] = ['rt.name' , 'like' , "%{$value}%"];
        }

        return self::from('xq_relation_tag as rt')
            ->select('rt.*' , DB::raw('count(rt.id) as total'))
            ->where($where)
            ->groupBy('rt.tag_id')
            ->orderBy('total' , 'desc')
            ->orderBy('rt.id' , 'asc')
            ->paginate($size);
    }

    // 热门标签-视频专题-分页
    public static function hotTagsWithPagerInVideoByValueAndFilterAndSize(string $value , array $filter = [] , int $size = 20): Paginator
    {
        $filter['module_id'] = $filter['module_id'] ?? '';
        $where = [
            ['rt.relation_type' , '=' , 'video'] ,
        ];
        if ($filter['module_id'] !== '') {
            $where[] = ['rt.module_id' , '=' , $filter['module_id']];
        }
        if ($filter['value'] !== '') {
            $where[] = ['rt.name' , 'like' , "%{$value}%"];
        }

        return self::from('xq_relation_tag as rt')
            ->select('rt.*' , DB::raw('count(rt.id) as total'))
            ->where($where)
            ->groupBy('rt.tag_id')
            ->orderBy('total' , 'desc')
            ->orderBy('rt.id' , 'asc')
            ->paginate($size);
    }

    public static function hotTagsInVideoByFilterAndSize(array $filter = [] , int $size = 20): Collection
    {
        $filter['module_id'] = $filter['module_id'] ?? '';

        $where = [
            ['relation_type' , '=' , 'video'] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['module_id' , '=' , $filter['module_id']];
        }

        return self::select('*' , DB::raw('count(id) as total'))
            ->where($where)
            ->groupBy('tag_id')
            ->orderBy('total' , 'desc')
            ->orderBy('id' , 'asc')
            ->limit($size)
            ->get();
    }
}
