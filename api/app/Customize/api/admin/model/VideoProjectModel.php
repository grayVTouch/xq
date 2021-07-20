<?php


namespace App\Customize\api\admin\model;


use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VideoProjectModel extends Model
{
    protected $table = 'xq_video_project';

    public static function index(array $filter = [] , array $order = [] , int $size = 20): Paginator
    {
        $filter['id']               = $filter['id'] ?? '';
        $filter['name']             = $filter['name'] ?? '';
        $filter['module_id']        = $filter['module_id'] ?? '';
        $filter['category_id']  = $filter['category_id'] ?? '';
        $filter['video_series_id']  = $filter['video_series_id'] ?? '';
        $filter['video_company_id'] = $filter['video_company_id'] ?? '';

        $order['field'] = $order['field'] ?? 'id';
        $order['value'] = $order['value'] ?? 'desc';

        $where = [];

        if ($filter['id'] !== '') {
            $where[] = ['id' , '=' , $filter['id']];
        }

        if ($filter['name'] !== '') {
            $where[] = ['name' , 'like' , "%{$filter['name']}%"];
        }

        if ($filter['module_id'] !== '') {
            $where[] = ['module_id' , '=' , $filter['module_id']];
        }

        if ($filter['category_id'] !== '') {
            $where[] = ['category_id' , '=' , $filter['category_id']];
        }

        if ($filter['video_series_id'] !== '') {
            $where[] = ['video_series_id' , '=' , $filter['video_series_id']];
        }

        if ($filter['video_company_id'] !== '') {
            $where[] = ['video_company_id' , '=' , $filter['video_company_id']];
        }

        return self::where($where)
            ->orderBy($order['field'] , $order['value'])
            ->paginate($size);
    }

    public static function search(int $module_id , string $value = '' , int $size = 20): Paginator
    {
        return self::where('module_id' , $module_id)
            ->where(function($query) use($value){
                $query->where('id' , $value)
                    ->orWhere('name' , 'like' , "%{$value}%");
            })
            ->orderBy('weight' , 'desc')
            ->orderBy('created_at' , 'desc')
            ->orderBy('id' , 'asc')
            ->paginate($size);
    }

    public static function findByName(string $name): ?VideoProjectModel
    {
        return self::where('name' , $name)->first();
    }

    public static function findByModuleIdAndName(int $module_id , string $name): ?VideoProjectModel
    {
        return self::where([
            ['module_id' , '=' , $module_id] ,
            ['name' , '=' , $name] ,
        ])->first();
    }

    public static function findByNameAndExcludeId(string $name , int $exclude_id): ?VideoProjectModel
    {
        return self::where([
            ['name' , '=' , $name] ,
            ['id' , '!=' , $exclude_id] ,
        ])->first();
    }

    public static function findByModuleIdAndExcludeIdAndName(int $module_id , int $exclude_id , string $name): ?VideoProjectModel
    {
        return self::where([
            ['id' , '!=' , $exclude_id] ,
            ['module_id' , '=' , $module_id] ,
            ['name' , '=' , $name] ,
        ])->first();
    }
}
