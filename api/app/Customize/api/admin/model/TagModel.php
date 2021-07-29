<?php


namespace App\Customize\api\admin\model;


use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TagModel extends Model
{
    protected $table = 'xq_tag';

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
        $filter['type']         = $filter['type'] ?? '';
        $filter['module_id']    = $filter['module_id'] ?? '';

        $order['field'] = $order['field'] ?? 'id';
        $order['value'] = $order['value'] ?? 'desc';

        $where = [];

        if ($filter['id'] !== '') {
            $where[] = ['id' , '=' , $filter['id']];
        }

        if ($filter['module_id'] !== '') {
            $where[] = ['module_id' , '=' , $filter['module_id']];
        }

        if ($filter['name'] !== '') {
            $where[] = ['name' , 'like' , "%{$filter['name']}%"];
        }

        if ($filter['type'] !== '') {
            $where[] = ['type' , '=' , $filter['type']];
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

    public static function topByModuleIdAndTypeAndSize(int $module_id , string $type , int $size = 10): Collection
    {
        return self::where([
                ['module_id' , '=' , $module_id] ,
                ['type' , '=' , $type] ,
            ])
            ->orderBy('count' , 'desc')
            ->limit($size)
            ->get();
    }

    public static function findByModuleIdAndNameAndType(int $module_id , string $name , string $type): ?TagModel
    {
        return self::where([
            ['module_id' , '=' , $module_id] ,
            ['name' , '=' , $name] ,
            ['type' , '=' , $type] ,
        ])->first();
    }

    public static function findByExcludeIdAndModuleIdAndNameAndType(int $exclude_id , int $module_id , string $name , string $type): ?TagModel
    {
        return self::where([
            ['id' , '!=' , $exclude_id] ,
            ['module_id' , '=' , $module_id] ,
            ['name' , '=' , $name] ,
            ['type' , '=' , $type] ,
        ])->first();
    }
}
