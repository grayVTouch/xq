<?php


namespace App\Customize\api\admin\model;


use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VideoSeriesModel extends Model
{
    protected $table = 'xq_video_series';

    public function user()
    {
        return $this->belongsTo(UserModel::class , 'user_id' , 'id');
    }

    public function module()
    {
        return $this->belongsTo(ModuleModel::class , 'module_id' , 'id');
    }

    public static function index(array $relation = [] , array $filter = [] , array $order = [] , int $size = 20): Paginator
    {
        $filter['id']           = $filter['id'] ?? '';
        $filter['name']         = $filter['name'] ?? '';
        $filter['module_id']    = $filter['module_id'] ?? '';
        $order['field']         = $order['field'] ?? 'id';
        $order['value']         = $order['value'] ?? 'desc';

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

        return self::with($relation)
            ->where($where)
            ->orderBy($order['field'] , $order['value'])
            ->paginate($size);
    }

    public static function search(array $relation , int $module_id , string $value = '' , int $size = 20): Paginator
    {
        return self::with($relation)
            ->where('module_id' , $module_id)
            ->where(function($query) use($value){
                $query->where('id' , $value)
                    ->orWhere('name' , 'like' , "%{$value}%");
            })
            ->orderBy('weight' , 'desc')
            ->orderBy('created_at' , 'desc')
            ->orderBy('id' , 'asc')
            ->paginate($size);
    }


    public static function findByName(string $name): ?VideoSeriesModel
    {
        return self::where('name' , $name)->first();
    }

    public static function findByNameAndExcludeId(string $name , int $exclude_id): ?VideoSeriesModel
    {
        return self::where([
            ['name' , '=' , $name] ,
            ['id' , '!=' , $exclude_id] ,
        ])->first();
    }
}
