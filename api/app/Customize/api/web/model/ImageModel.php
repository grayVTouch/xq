<?php


namespace App\Customize\api\web\model;


use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ImageModel extends Model
{
    protected $table = 'xq_image';

    public static function getByImageProjectId(int $image_subject_id): Collection
    {
        return self::where('image_project_id' , $image_subject_id)->get();
    }

    public static function getNewestByFilterAndSize(array $filter = [] , int $size = 0): Collection
    {
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['type']      = $filter['type'] ?? '';

        $where = [
            ['ip.status' , '=' , 1] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['i.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['ip.type' , '=' , $filter['type']];
        }

        return self::select([
                'i.*'
            ])
            ->where($where)
            ->from('xq_image as i')
            ->leftJoin('xq_image_project as ip' , 'i.image_project_id' , '=' , 'ip.id')
            ->orderBy('i.created_at' , 'desc')
            ->orderBy('i.id' , 'desc')
            ->limit($size)
            ->get();
    }

    public static function getHotByFilterAndSize(array $filter = [] , int $size = 0): Collection
    {
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['type']      = $filter['type'] ?? '';

        $where = [
            ['ip.status' , '=' , 1] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['i.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['ip.type' , '=' , $filter['type']];
        }

        return self::select([
            'i.*'
        ])
            ->where($where)
            ->from('xq_image as i')
            ->leftJoin('xq_image_project as ip' , 'i.image_project_id' , '=' , 'ip.id')
            ->orderBy('i.view_count' , 'desc')
            ->orderBy('i.praise_count' , 'desc')
            ->orderBy('i.collect_count' , 'desc')
            ->orderBy('i.created_at' , 'desc')
            ->orderBy('i.id' , 'desc')
            ->limit($size)
            ->get();
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
        return self::select(['i.*'])
            ->from('xq_image as i')
            ->leftJoin('xq_image_project as ip' , 'i.image_project_id' , '=' , 'ip.id')
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

    public static function getWithPagerInStrictByFilterAndOrderAndSize(array $filter = [] , $order = null , int $size = 20)
    {
        $filter['value']        = $filter['value'] ?? '';
        $filter['module_id']    = $filter['module_id'] ?? '';
        $filter['type']         = $filter['type'] ?? '';
        $filter['category_ids'] = $filter['category_ids'] ?? [];
        $filter['tag_ids']      = $filter['tag_ids'] ?? [];

        $order = $order ?? ['field' => 'created_at' , 'value' => 'desc'];
        $value = strtolower($filter['value']);

        $where = [
            ['ip.status' , '=' , 1] ,
            ['ip.name' , 'like' , "%{$value}%"] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['i.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['ip.type' , '=' , $filter['type']];
        }

        $query = self::select(['i.*'])
            ->from('xq_image as i')
            ->leftJoin('xq_image_project as ip' , 'i.image_project_id' , '=' , 'ip.id')
            ->where($where);

        if (!empty($filter['category_ids'])) {
            $query->whereIn('i.category_id' , $filter['category_ids']);
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
            ->orderBy("i.{$order['field']}" , $order['value'])
            ->orderBy('i.id' , 'desc')
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
            ['i.status' , '=' , 1] ,
            ['ip.name' , 'like' , "%{$value}%"] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['i.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['type'] !== '') {
            $where[] = ['ip.type' , '=' , $filter['type']];
        }

        $query = self::select()
            ->from('xq_image as i')
            ->leftJoin('xq_image_project as ip' , 'ip.id' , '=' , 'i.image_project_id')
            ->where($where);

        if (!empty($filter['category_ids'])) {
            $query->whereIn('ip.category_id' , $filter['category_ids']);
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
            ->orderBy("i.{$order['field']}" , $order['value'])
            ->orderBy('i.id' , 'desc')
            ->paginate($size);
    }

    public static function recommendExcludeSelfByFilterAndSize(int $self_id , array $filter = [] , int $size = 20): Collection
    {
        $filter['module_id']    = $filter['module_id'] ?? '';
        $filter['category_id']  = $filter['category_id'] ?? '';

        $where = [
            ['i.id' , '!=' , $self_id] ,
            ['ip.type' , '=' , 'misc'] ,
        ];

        if ($filter['module_id'] !== '') {
            $where[] = ['i.module_id' , '=' , $filter['module_id']];
        }

        if ($filter['category_id'] !== '') {
            $where[] = ['i.category_id' , '=' , $filter['category_id']];
        }

        return self::select(['i.*'])
            ->from('xq_image as i')
            ->leftJoin('xq_image_project as ip' , 'i.image_project_id' , '=' , 'ip.id')
            ->where($where)
            ->orderBy('i.collect_count' , 'desc')
            ->orderBy('i.praise_count' , 'desc')
            ->orderBy('i.view_count' , 'desc')
            ->orderBy('i.created_at' , 'desc')
            ->orderBy('i.id' , 'desc')
            ->limit($size)
            ->get();
    }

    public static function countByImageProjectId(int $image_project_id): int
    {
        return (int) (self::where('image_project_id' , $image_project_id)->count());
    }
}
