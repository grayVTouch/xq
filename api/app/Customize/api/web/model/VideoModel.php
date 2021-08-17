<?php


namespace App\Customize\api\web\model;


use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class VideoModel extends Model
{
    protected $table = 'xq_video';

    public function user()
    {
        return $this->belongsTo(UserModel::class , 'user_id' , 'id');
    }

    public function tags()
    {
        return $this->hasMany(RelationTagModel::class , 'relation_id' , 'id');
    }


    public static function getByVideoProjectId(int $video_project_id): Collection
    {
        return self::where([
                ['status' , '=' , 1] ,
                ['video_project_id' , '=' , $video_project_id]
            ])
            ->orderBy('index' , 'asc')
            ->orderBy('id' , 'asc')
            ->get();
    }

    public static function getByVideoProjectIdAndMinIndexAndMaxIndex(int $video_project_id , int $min , int $max): Collection
    {
        return self::where([
                ['status' , '=' , 1] ,
                ['video_project_id' , '=' , $video_project_id] ,
                ['index' , '>=' , $min] ,
                ['index' , '<=' , $max] ,
            ])
            ->orderBy('index' , 'asc')
            ->get();
    }

    public static function getNewestByRelationAndFilterAndSize(array $relation = [] , array $filter = [] , int $size = 0): Collection
    {
        $filter['module_id'] = $filter['module_id'] ?? '';
        $where = [
            ['type' , '=' , 'misc'] ,
            ['status' , '=' , 1] ,
        ];
        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with[$v] = function($query){
                    $query->where('relation_type' , 'video');
                };
                continue ;
            }
            $with[] = $v;
        }
        if ($filter['module_id'] !== '') {
            $where[] = ['module_id' , '=' , $filter['module_id']];
        }
        return self::with($relation)
            ->where($where)
            ->orderBy('created_at' , 'desc')
            ->orderBy('id' , 'asc')
            ->limit($size)
            ->get();
    }

    public static function getRecommendByRelationAndFieldAndFilterAndSize(array $relation = [] , array $field = null , array $filter = [] , int $size = 20): Paginator
    {
        $field = $field ?? '*';
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['exclude_id'] = $filter['exclude_id'] ?? '';
        $filter['category_id'] = $filter['category_id'] ?? '';
        $filter['video_subject_id'] = $filter['video_subject_id'] ?? '';

        $where = [
            ['type' , '=' , 'misc'] ,
            ['status' , '=' , 1] ,
        ];
        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with[$v] = function($query){
                    $query->where('relation_type' , 'video');
                };
                continue ;
            }
            $with[] = $v;
        }
        if ($filter['module_id'] !== '') {
            $where[] = ['module_id' , '=' , $filter['module_id']];
        }
        if ($filter['exclude_id'] !== '') {
            $where[] = ['id' , '!=' , $filter['exclude_id']];
        }
        if ($filter['category_id'] !== '') {
            $where[] = ['category_id' , '=' , $filter['category_id']];
        }
        if ($filter['video_subject_id'] !== '') {
            $where[] = ['video_subject_id' , '=' , $filter['video_subject_id']];
        }
        return self::with($with)
            ->select($field)
            ->where($where)
            ->orderBy('collect_count' , 'desc')
            ->orderBy('praise_count' , 'desc')
            ->orderBy('view_count' , 'desc')
            ->orderBy('created_at' , 'desc')
            ->orderBy('id' , 'desc')
            ->paginate($size);
    }

    public static function getHotByRelationAndFilterAndSize(array $relation = [] , array $filter = [] , int $size = 0): Collection
    {
        $filter['module_id'] = $filter['module_id'] ?? '';

        $where = [
            ['type' , '=' , 'misc'] ,
            ['status' , '=' , 1] ,
        ];
        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with[$v] = function($query){
                    $query->where('relation_type' , 'video');
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
            ->orderBy('collect_count' , 'desc')
            ->orderBy('praise_count' , 'desc')
            ->orderBy('play_count' , 'desc')
            ->orderBy('view_count' , 'desc')
            ->orderBy('created_at' , 'desc')
            ->orderBy('id' , 'asc')
            ->limit($size)
            ->get();
    }

    public static function getByTagIdAndRelationAndTagIdAndFilterAndSize(array $relation , int $tag_id , array $filter = [] , int $size = 0): Collection
    {
        $filter['module_id'] = $filter['module_id'] ?? '';

        $where = [
            ['v.type' , '=' , 'misc'] ,
            ['v.status' , '=' , 1] ,
        ];
        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with[$v] = function($query){
                    $query->where('relation_type' , 'video');
                };
                continue ;
            }
            $with[] = $v;
        }

        if ($filter['module_id'] !== '') {
            $where[] = ['v.module_id' , '=' , $filter['module_id']];
        }

        return self::with($with)
            ->from('xq_video as v')
            ->select('v.*')
            ->where($where)
            ->whereExists(function($query) use($tag_id){
                $query->from('xq_relation_tag')
                    ->where([
                        ['tag_id' , '=' , $tag_id] ,
                        ['relation_type' , '=' , 'video'] ,
                    ])
                    ->whereRaw('v.id = relation_id');
            })
            ->orderBy('v.created_at' , 'desc')
            ->orderBy('v.id' , 'asc')
            ->limit($size)
            ->get();
    }

    public static function getWithPagerInStrictByRelationAndFilterAndOrderAndSize(array $relation = [] , array $filter = [] , $order = null , int $size = 20)
    {
        $filter['value']        = $filter['value'] ?? '';
        $filter['module_id']    = $filter['module_id'] ?? '';
        $filter['tag_ids']      = $filter['tag_ids'] ?? [];
        $filter['category_ids']      = $filter['category_ids'] ?? [];

        $order = $order ?? ['field' => 'created_at' , 'value' => 'desc'];

        $where = [
            ['v.type' , '=' , 'misc'] ,
            ['v.status' , '=' , 1] ,
        ];
        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with[$v] = function($query){
                    $query->where('relation_type' , 'video');
                };
                continue ;
            }
            $with[] = $v;
        }

        if ($filter['module_id'] !== '') {
            $where[] = ['v.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['value'] !== '') {
            $where[] = ['v.name' , 'like' , "%{$filter['value']}%"];
        }

        $query = self::with($with)
            ->from('xq_video as v')
            ->where($where);

        if (!empty($filter['category_ids'])) {
            $query->whereIn('v.category_id' , $filter['category_ids']);
        }

        if (!empty($filter['tag_ids'])) {
            $query->whereExists(function($query) use($filter){
                $query->select('id')
                    ->selectRaw('count(id) as total')
                    ->from('xq_relation_tag')
                    ->where([
                        ['relation_type' , '=' , 'video'] ,
                    ])
                    ->whereIn('tag_id' , $filter['tag_ids'])
                    ->groupBy('relation_id')
                    ->having('total' , '=' , count($filter['tag_ids']))
                    ->whereRaw('relation_id = v.id');
            });
        }

        return $query->orderBy("v.{$order['field']}" , $order['value'])
            ->orderBy('v.id' , 'desc')
            ->paginate($size);
    }

    public static function getWithPagerInLooseByRelationAndFilterAndOrderAndSize(array $relation = [] , array $filter = [] , $order = null , int $size = 20)
    {
        $filter['value']        = $filter['value'] ?? '';
        $filter['module_id']    = $filter['module_id'] ?? '';
        $filter['tag_ids']      = $filter['tag_ids'] ?? [];
        $filter['category_ids']      = $filter['category_ids'] ?? [];

        $order = $order ?? ['field' => 'created_at' , 'value' => 'desc'];

        $where = [
            ['v.type' , '=' , 'misc'] ,
            ['v.status' , '=' , 1] ,
        ];

        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with[$v] = function($query){
                    $query->where('relation_type' , 'video');
                };
                continue ;
            }
            $with[] = $v;
        }

        if ($filter['module_id'] !== '') {
            $where[] = ['v.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['value'] !== '') {
            $where[] = ['v.name' , 'like' , "%{$filter['value']}%"];
        }

        $query = self::with($with)
            ->from('xq_video as v')
            ->where($where);

        if (!empty($filter['category_ids'])) {
            $query->whereIn('v.category_id' , $filter['category_ids']);
        }

        if (!empty($filter['tag_ids'])) {
            $query->whereExists(function($query) use($filter){
                $query->select('id')
                    ->from('xq_relation_tag')
                    ->where([
                        ['relation_type' , '=' , 'video'] ,
                    ])
                    ->whereRaw('relation_id = vp.id')
                    ->whereIn('tag_id' , $filter['tag_ids']);
            });
        }

        return $query->orderBy("v.{$order['field']}" , $order['value'])
            ->orderBy('v.id' , 'desc')
            ->paginate($size);
    }
}
