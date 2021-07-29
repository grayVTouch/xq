<?php


namespace App\Customize\api\web\model;


use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ImageProjectModel extends Model
{
    protected $table = 'xq_image_project';

    public function user()
    {
        return $this->belongsTo(UserModel::class , 'user_id' , 'id');
    }

    public function module()
    {
        return $this->belongsTo(ModuleModel::class , 'module_id' , 'id');
    }

    public function tags()
    {
        return $this->hasMany(RelationTagModel::class , 'relation_id' , 'id');
    }

    public function images()
    {
        return $this->hasMany(ImageModel::class , 'image_project_id' , 'id');
    }

    public static function getNewestByRelationAndFieldAndFilterAndSize(array $relation = [] , array $field = null , array $filter = [] , int $size = 0): Collection
    {
        $field = $field ?? '*';
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['type']      = $filter['type'] ?? '';

        $where = [
            ['status' , '=' , 1] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['type' , '=' , $filter['type']];
        }
        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with['tags'] = function($query){
                    $query->where('relation_type' , 'image_project');
                };
                continue ;
            }
            $with[] = $v;
        }
        return self::with($with)
            ->select($field)
            ->where($where)
            ->orderBy('created_at' , 'desc')
            ->orderBy('id' , 'asc')
            ->limit($size)
            ->get();
    }

    public static function getHotByRelationAndFieldAndFilterAndSize(array $relation = [] , array $field = null , array $filter = [] , int $size = 0): Collection
    {
        $field = $field ?? '*';
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['type']      = $filter['type'] ?? '';

        $where = [
            ['status' , '=' , 1] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['type' , '=' , $filter['type']];
        }
        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with['tags'] = function($query){
                    $query->where('relation_type' , 'image_project');
                };
                continue ;
            }
            $with[] = $v;
        }
        return self::with($with)
            ->select($field)
            ->where($where)
            ->orderBy('collect_count' , 'desc')
            ->orderBy('praise_count' , 'desc')
            ->orderBy('view_count' , 'desc')
            ->orderBy('created_at' , 'desc')
            ->orderBy('id' , 'asc')
            ->limit($size)
            ->get();
    }

    public static function getHotWithPagerByRelationAndFilterAndSize(array $relation = [] , array $filter = [] , int $size = 0): Paginator
    {
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['type']      = $filter['type'] ?? '';

        $where = [
            ['status' , '=' , 1] ,
        ];

        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with['tags'] = function($query){
                    $query->where('relation_type' , 'image_project');
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
            // 查看次数
            ->orderBy('view_count' , 'desc')
            // 点赞次数
            ->orderBy('praise_count' , 'desc')
            // todo 收藏次数
            // id 倒叙排序
            ->orderBy('created_at' , 'desc')
            ->orderBy('id' , 'asc')
            ->paginate($size);
    }


    public static function getByRelationAndFieldAndTagIdAndFilterAndSize(array $relation , ?array $field , int $tag_id , array $filter = [] , int $size = 0): Collection
    {
        $field = $field ?? 'ip.*';
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['type']      = $filter['type'] ?? '';

        if (is_array($field)) {
            array_walk($field , function (&$v) {
                $v = 'ip.' . $v;
            });
        }

        $where = [
            ['ip.status' , '=' , 1] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['ip.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['ip.type' , '=' , $filter['type']];
        }

        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with['tags'] = function($query){
                    $query->where('relation_type' , 'image_project');
                };
                continue ;
            }
            $with[] = $v;
        }
        return self::with($with)
            ->from('xq_image_project as ip')
            ->select($field)
            ->where($where)
            ->whereExists(function($query) use($tag_id){
                $query->select('id')
                    ->from('xq_relation_tag as rt')
                    ->where([
                        ['tag_id' , '=' , $tag_id] ,
                        ['relation_type' , '=' , 'image_project'] ,
                    ])
                    ->whereRaw(DB::Raw('ip.id = rt.relation_id'));
            })
            ->orderBy('ip.created_at' , 'desc')
            ->orderBy('ip.id' , 'asc')
            ->limit($size)
            ->get();
    }

    // 标签对应的图片专题-非严格模式匹配
    public static function getInLooseByRelationAndTagIdsAndFilterAndSize(array $relation = [] , array $tag_ids = [] , array $filter = [] , int $size = 0): Paginator
    {
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['type']      = $filter['type'] ?? '';

        $where = [
            ['ip.status' , '=' , 1] ,
        ];

        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with['tags'] = function($query){
                    $query->where('relation_type' , 'image_project');
                };
                continue ;
            }
            $with[] = $v;
        }

        if ($filter['module_id'] !== '') {
            $where[] = ['ip.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['ip.type' , '=' , $filter['type']];
        }

        return self::with($with)
            ->from('xq_image_project as ip')
            ->select('ip.*')
            ->where($where)
            ->whereExists(function($query) use($tag_ids){
                $query->select('id')
                    ->from('xq_relation_tag as rt')
                    ->where([
                        ['relation_type' , '=' , 'image_project'] ,
                    ])
                    ->whereIn('tag_id' , $tag_ids)
                    ->whereRaw('ip.id = rt.relation_id');
            })
            ->orderBy('ip.created_at' , 'desc')
            ->orderBy('ip.id' , 'asc')
            ->paginate($size);
    }

    // 标签对应的图片专题-严格模式匹配
    public static function getInStrictByRelationAndTagIdsAndFilterAndSize(array $relation = [] , array $tag_ids = [] , array $filter = [] , int $size = 0): Paginator
    {
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['type']      = $filter['type'] ?? '';

        $where = [
            ['ip.status' , '=' , 1] ,
        ];

        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with['tags'] = function($query){
                    $query->where('relation_type' , 'image_project');
                };
                continue ;
            }
            $with[] = $v;
        }

        if ($filter['module_id'] !== '') {
            $where[] = ['ip.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['ip.type' , '=' , $filter['type']];
        }

        return self::with($with)
            ->from('xq_image_project as ip')
            ->select('ip.*')
            ->where($where)
            ->whereExists(function($query) use($tag_ids){
                $query->select('id')
                    ->selectRaw('count(id) as total')
                    ->from('xq_relation_tag as rt')
                    ->where([
                        ['relation_type' , '=' , 'image_project'] ,
                    ])
                    ->whereIn('tag_id' , $tag_ids)
                    ->whereRaw('ip.id = rt.relation_id')
                    ->groupBy('relation_id')
                    ->having('total' , '=' , count($tag_ids));
            })
            ->orderBy('ip.created_at' , 'desc')
            ->orderBy('ip.id' , 'asc')
            ->paginate($size);
    }

    public static function getNewestWithPagerByRelationAndFieldAndFilterAndSize(array $relation , ?array $field , array $filter = [] , int $size = 0): Paginator
    {
        $field = $field ?? '*';
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['type']      = $filter['type'] ?? '';

        $where = [
            ['status' , '=' , 1] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['type' , '=' , $filter['type']];
        }

        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with['tags'] = function($query){
                    $query->where('relation_type' , 'image_project');
                };
                continue ;
            }
            $with[] = $v;
        }

        return self::with($with)
            ->select($field)
            ->where($where)
            ->orderBy('created_at' , 'desc')
            ->orderBy('id' , 'asc')
            ->paginate($size);
    }

    public static function getWithPagerInStrictByRelationAndFilterAndOrderAndSize(array $relation = [] , array $filter = [] , $order = null , int $size = 20)
    {
        $filter['value']        = $filter['value'] ?? '';
        $filter['module_id']    = $filter['module_id'] ?? '';
        $filter['type']         = $filter['type'] ?? '';
        $filter['category_ids'] = $filter['category_ids'] ?? [];
        $filter['image_subject_ids']  = $filter['image_subject_ids'] ?? [];
        $filter['tag_ids']      = $filter['tag_ids'] ?? [];

        $order = $order ?? ['field' => 'created_at' , 'value' => 'desc'];
        $value = strtolower($filter['value']);

        $where = [
            ['ip.status' , '=' , 1] ,
            ['ip.name' , 'like' , "%{$value}%"] ,
        ];

        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with['tags'] = function($query){
                    $query->where('relation_type' , 'image_project');
                };
                continue ;
            }
            $with[] = $v;
        }

        if ($filter['module_id'] !== '') {
            $where[] = ['ip.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['ip.type' , '=' , $filter['type']];
        }

        $query = self::with($with)
            ->from('xq_image_project as ip')
            ->where($where);

        if (!empty($filter['category_ids'])) {
            $query->whereIn('category_id' , $filter['category_ids']);
        }

        if (!empty($filter['image_subject_ids'])) {
            $query->whereIn('ip.image_subject_id' , $filter['image_subject_ids']);
        }

        if (!empty($filter['tag_ids'])) {
            $query->whereExists(function($query) use($filter){
                $query->select('*' , DB::raw('count(id) as total'))
                    ->from('xq_relation_tag')
                    ->where([
                        ['relation_type' , '=' , 'image_project'] ,
                    ])
                    ->whereIn('tag_id' , $filter['tag_ids'])
                    ->groupBy('relation_id')
                    ->having('total' , '=' , count($filter['tag_ids']))
                    ->whereRaw('relation_id = ip.id');
            });
        }
        return $query->orderBy("ip.{$order['field']}" , $order['value'])
            ->orderBy('ip.id' , 'desc')
            ->paginate($size);
    }

    public static function getWithPagerInLooseByRelationAndFilterAndOrderAndSize(array $relation = [] , array $filter = [] , $order = null , int $size = 20)
    {
        $filter['value']        = $filter['value'] ?? '';
        $filter['module_id']    = $filter['module_id'] ?? '';
        $filter['type']         = $filter['type'] ?? '';
        $filter['category_ids'] = $filter['category_ids'] ?? [];
        $filter['image_subject_ids']  = $filter['image_subject_ids'] ?? [];
        $filter['tag_ids']      = $filter['tag_ids'] ?? [];

        $order = $order ?? ['field' => 'created_at' , 'value' => 'desc'];
        $value = strtolower($filter['value']);

        $where = [
            ['ip.status' , '=' , 1] ,
            ['ip.name' , 'like' , "%{$value}%"] ,
        ];

        $with = [];
        foreach ($relation as $v)
        {
            if ($v === 'tags') {
                $with['tags'] = function($query){
                    $query->where('relation_type' , 'image_project');
                };
                continue ;
            }
            $with[] = $v;
        }

        if ($filter['module_id'] !== '') {
            $where[] = ['ip.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['ip.type' , '=' , $filter['type']];
        }

        $query = self::with($with)
            ->from('xq_image_project as ip')
            ->where($where);

        if (!empty($filter['category_ids'])) {
            $query->whereIn('category_id' , $filter['category_ids']);
        }

        if (!empty($filter['image_subject_ids'])) {
            $query->whereIn('ip.image_subject_id' , $filter['image_subject_ids']);
        }

        return $query->whereExists(function($query) use($filter){
                if (empty($filter['tag_ids'])) {
                    return ;
                }
                $query->from('xq_relation_tag')
                    ->where([
                        ['relation_type' , '=' , 'image_project'] ,
                    ])
                    ->whereIn('tag_id' , $filter['tag_ids'])
                    ->whereRaw('relation_id = ip.id');
            })
            ->orderBy("ip.{$order['field']}" , $order['value'])
            ->orderBy('ip.id' , 'desc')
            ->paginate($size);
    }

    public static function countHandle(int $id , string $field , string $mode = '' , int $step = 1)
    {
        return self::where('id' , $id)->$mode($field , $step);
    }

    public static function recommendExcludeSelfByFilterAndSize(int $self_id , array $filter = [] , int $size = 20): Collection
    {
        $filter['module_id']    = $filter['module_id'] ?? '';
        $filter['category_id']  = $filter['category_id'] ?? '';
        $filter['image_subject_id']   = $filter['image_subject_id'] ?? '';
        $filter['type']         = $filter['type'] ?? '';

        $where = [
            ['id' , '!=' , $self_id] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['module_id' , '=' , $filter['module_id']];
        }

        if ($filter['category_id'] !== '') {
            $where[] = ['category_id' , '=' , $filter['category_id']];
        }

        if ($filter['image_subject_id'] !== '') {
            $where[] = ['image_subject_id' , '=' , $filter['image_subject_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['type' , '=' , $filter['type']];
        }

        return self::where($where)
            ->orderBy('view_count' , 'desc')
            ->orderBy('praise_count' , 'desc')
            ->orderBy('created_at' , 'desc')
            ->limit($size)
            ->get();
    }

}
