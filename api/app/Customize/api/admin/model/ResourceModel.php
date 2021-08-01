<?php

namespace App\Customize\api\admin\model;


use Illuminate\Database\Eloquent\Collection;

class ResourceModel extends Model
{
    //
    protected $table = 'xq_resource';

    public static function updateByUrlOrPath(string $value , array $data = [])
    {
        return self::where(function($query) use($value){
                $query->where('url' , $value)
                    ->orWhere('path' , $value);
            })
            ->update($data);
    }

    public static function findByUrlOrPath(string $value): ?ResourceModel
    {
        return self::where(function($query) use($value){
                $query->where('url' , $value)
                    ->orWhere('path' , $value);
            })
            ->first();
    }

    public static function getWaitDeleteByLimitIdAndSize(int $limit_id = 0 , int $size = 20): Collection
    {
        return self::where(function($query){
            $query->where('is_used' , 0)
                ->orWhere('is_deleted' , 1);
        })
            ->where([
                ['id' , '>' , $limit_id] ,
                ['status' , '=' , 0] ,
            ])
            ->limit($size)
            ->get();
    }

}
