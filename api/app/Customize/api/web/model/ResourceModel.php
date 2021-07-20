<?php

namespace App\Customize\api\web\model;


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

}
