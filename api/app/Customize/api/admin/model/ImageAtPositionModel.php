<?php


namespace App\Customize\api\admin\model;


use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ImageAtPositionModel extends Model
{
    protected $table = 'xq_image_at_position';

    public function module()
    {
        return $this->belongsTo(ModuleModel::class , 'module_id' , 'id');
    }

    public function position()
    {
        return $this->belongsTo(PositionModel::class , 'position_id' , 'id');
    }

    public static function index(array $relation = [] , array $filter = [] , array $order = [] , int $size = 20): Paginator
    {
        $filter['id'] = $filter['id'] ?? '';
        $filter['name'] = $filter['name'] ?? '';
        $filter['module_id'] = $filter['module_id'] ?? '';
        $filter['platform'] = $filter['platform'] ?? '';
        $filter['position_id'] = $filter['position_id'] ?? '';
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
        if ($filter['platform'] !== '') {
            $where[] = ['platform' , '=' , $filter['platform']];
        }
        if ($filter['position_id'] !== '') {
            $where[] = ['position_id' , '=' , $filter['position_id']];
        }
        return self::with($relation)
            ->where($where)
            ->orderBy($order['field'] , $order['value'])
            ->paginate($size);
    }

    public static function search(string $value = ''): Collection
    {
        return self::where('id' , $value)
            ->orWhere('name' , $value)
            ->get();
    }

    public static function top(int $size = 10): Collection
    {
        return self::orderBy('count' , 'desc')
            ->limit($size)
            ->get();
    }

    public static function getByName(string $name = ''): ?TagModel
    {
        return self::where('name' , $name)->first();
    }
}
