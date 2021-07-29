<?php


namespace App\Customize\api\admin\model;


use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VideoCompanyModel extends Model
{
    protected $table = 'xq_video_company';

    public function user()
    {
        return $this->belongsTo(UserModel::class , 'user_id' , 'id');
    }

    public function module()
    {
        return $this->belongsTo(ModuleModel::class , 'module_id' , 'id');
    }

    public function region()
    {
        return $this->belongsTo(RegionModel::class , 'country_id' , 'id');
    }

    public static function index(array $relation = [] , array $filter = [] , array $order = [] , int $size = 20): Paginator
    {
        $filter['id']           = $filter['id'] ?? '';
        $filter['name']         = $filter['name'] ?? '';
        $filter['module_id']    = $filter['module_id'] ?? '';
        $filter['country_id']   = $filter['country_id'] ?? '';

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

        if ($filter['country_id'] !== '') {
            $where[] = ['country_id' , '=' , $filter['country_id']];
        }

        return self::with($relation)
            ->where($where)
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

    public static function findByName(string $name): ?VideoCompanyModel
    {
        return self::where('name' , $name)->first();
    }

    public static function findByNameAndExcludeId(string $name , int $exclude_id): ?VideoCompanyModel
    {
        return self::where([
            ['name' , '=' , $name] ,
            ['id' , '!=' , $exclude_id] ,
        ])->first();
    }

}
