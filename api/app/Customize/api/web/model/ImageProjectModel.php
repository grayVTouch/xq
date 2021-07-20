<?php


namespace App\Customize\api\web\model;


use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ImageProjectModel extends Model
{
    protected $table = 'xq_image_project';

    public static function getNewestByFilterAndSize(array $filter = [] , int $size = 0): Collection
    {
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

        return self::where($where)
            ->orderBy('created_at' , 'desc')
            ->orderBy('id' , 'asc')
            ->limit($size)
            ->get();
    }

    public static function getHotByFilterAndSize(array $filter = [] , int $size = 0): Collection
    {
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
        return self::where($where)
            ->orderBy('collect_count' , 'desc')
            ->orderBy('praise_count' , 'desc')
            ->orderBy('view_count' , 'desc')
            ->orderBy('created_at' , 'desc')
            ->orderBy('id' , 'asc')
            ->limit($size)
            ->get();
    }

    public static function getHotWithPagerByFilterAndSize(array $filter = [] , int $size = 0): Paginator
    {
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
        return self::where($where)
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


    public static function getByTagIdAndFilterAndSize(int $tag_id , array $filter = [] , int $size = 0): Collection
    {
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['type']      = $filter['type'] ?? '';

        $where = [
            ['ip.status' , '=' , 1] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['ip.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['ip.type' , '=' , $filter['type']];
        }
        return self::from('xq_image_project as ip')
            ->select('ip.*')
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
    public static function getByTagIdsAndFilterAndSize(array $tag_ids = [] , array $filter = [] , int $size = 0): Paginator
    {
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['type']      = $filter['type'] ?? '';

        $where = [
            ['ip.status' , '=' , 1] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['ip.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['ip.type' , '=' , $filter['type']];
        }

        return self::from('xq_image_project as ip')
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
    public static function getInStrictByTagIdsAndFilterAndSize(array $tag_ids = [] , array $filter = [] , int $size = 0): Paginator
    {
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['type']      = $filter['type'] ?? '';

        $where = [
            ['ip.status' , '=' , 1] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['ip.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['ip.type' , '=' , $filter['type']];
        }

        return self::from('xq_image_project as ip')
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

    public static function getNewestWithPagerByFilterAndSize(array $filter = [] , int $size = 0): Paginator
    {
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

        return self::where($where)
            ->orderBy('created_at' , 'desc')
            ->orderBy('id' , 'asc')
            ->paginate($size);
    }

    public static function getWithPagerInStrictByFilterAndOrderAndSize(array $filter = [] , $order = null , int $size = 20)
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

        if ($filter['module_id'] !== '') {
            $where[] = ['ip.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['ip.type' , '=' , $filter['type']];
        }

        $query = self::from('xq_image_project as ip')
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
                $query->select('*' , DB::raw('count(id) as total'))
                    ->from('xq_relation_tag')
                    ->where([
                        ['relation_type' , '=' , 'image_project'] ,
                    ])
                    ->whereIn('tag_id' , $filter['tag_ids'])
                    ->groupBy('relation_id')
                    ->having('total' , '=' , count($filter['tag_ids']))
                    ->whereRaw('relation_id = ip.id');
            })
            ->orderBy("ip.{$order['field']}" , $order['value'])
            ->orderBy('ip.id' , 'desc')
            ->paginate($size);
    }

    public static function getWithPagerInLooseByFilterAndOrderAndSize(array $filter = [] , $order = null , int $size = 20)
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

        if ($filter['module_id'] !== '') {
            $where[] = ['ip.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['ip.type' , '=' , $filter['type']];
        }

        $query = self::from('xq_image_project as ip')
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
