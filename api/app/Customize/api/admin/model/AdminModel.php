<?php


namespace App\Customize\api\admin\model;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class AdminModel extends Model
{
    protected $table = 'xq_admin';

    public function role()
    {
        return $this->belongsTo(RoleModel::class , 'role_id' , 'id');
    }

    public static function findByUsername(string $username = '')
    {
        return self::where('username' , $username)
            ->first();
    }

    public static function search(string $value = ''): Collection
    {
        return self::where('id' , $value)
            ->orWhere('username' , $value)
            ->orWhere('phone' , $value)
            ->orWhere('email' , $value)
            ->get();
    }

    public static function index(array $relation = [] , array $filter = [] , array $order = [] , int $size = 20): Paginator
    {
        $filter['id']       = $filter['id'] ?? '';
        $filter['username'] = $filter['username'] ?? '';
        $filter['role_id']  = $filter['role_id'] ?? '';
        $filter['sex']      = $filter['sex'] ?? '';
        $filter['phone']    = $filter['phone'] ?? '';
        $filter['email']    = $filter['email'] ?? '';
        $filter['is_root']  = $filter['is_root'] ?? '';
        $order['field']     = $order['field'] ?? 'id';
        $order['value']     = $order['value'] ?? 'asc';

        $where = [];

        if ($filter['id'] !== '') {
            $where[] = ['id' , '=' , $filter['id']];
        }

        if ($filter['username'] !== '') {
            $where[] = ['username' , 'like' , "%{$filter['username']}%"];
        }

        if ($filter['role_id'] !== '') {
            $where[] = ['role_id' , '=' , $filter['role_id']];
        }

        if ($filter['sex'] !== '') {
            $where[] = ['sex' , '=' , $filter['sex']];
        }

        if ($filter['phone'] !== '') {
            $where[] = ['phone' , 'like' , "%{$filter['phone']}%"];
        }

        if ($filter['email'] !== '') {
            $where[] = ['email' , 'like' , "%{$filter['email']}%"];
        }

        if ($filter['is_root'] !== '') {
            $where[] = ['is_root' , '=' , $filter['is_root']];
        }

        return self::with($relation)
            ->where($where)
            ->orderBy($order['field'] , $order['value'])
            ->paginate($size);
    }


}
