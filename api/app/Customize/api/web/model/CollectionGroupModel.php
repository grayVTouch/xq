<?php


namespace App\Customize\api\web\model;


use Illuminate\Database\Eloquent\Collection;

class CollectionGroupModel extends Model
{
    protected $table = 'xq_collection_group';

    public static function findByModuleIdAndUserIdAndName(int $module_id , int $user_id , string $name): ?CollectionGroupModel
    {
        return self::where([
            ['module_id' , '=' , $module_id] ,
            ['user_id' , '=' , $user_id] ,
            ['name' , '=' , $name] ,
            ])->first();
    }

    public static function findByModuleIdAndUserIdAndNameExcludeIds(int $module_id , int $user_id , string $name , array $exclude_ids = []): ?CollectionGroupModel
    {
        return self::where([
                ['module_id' , '=' , $module_id] ,
                ['user_id' , '=' , $user_id] ,
                ['name' , '=' , $name] ,
            ])
            ->whereNotIn('id' , $exclude_ids)
            ->first();
    }


    public static function getByModuleIdAndUserIdAndValue(int $module_id , int $user_id , string $value = ''): Collection
    {

        return self::where([
                ['module_id' , '=' , $module_id] ,
                ['user_id' , '=' , $user_id] ,
                ['name' , 'like' , "%{$value}%"] ,
            ])
            ->orderBy('created_at' , 'desc')
            ->get();
    }

    public static function getByModuleIdAndUserId(int $module_id , int $user_id): Collection
    {

        return self::where([
                ['module_id' , '=' , $module_id] ,
                ['user_id' , '=' , $user_id] ,
            ])
            ->orderBy('id' , 'desc')
            ->get();
    }

    public static function getByModuleIdAndUserIdAndSize(int $module_id , int $user_id , int $size = 20): Collection
    {
        return self::where([
                ['module_id' , '=' , $module_id] ,
                ['user_id' , '=' , $user_id] ,
            ])
            ->limit($size)
            ->get();
    }

    public static function countByModuleIdAndUserId(int $module_id , int $user_id): int
    {
        return self::where([
                ['module_id' , '=' , $module_id] ,
                ['user_id' , '=' , $user_id] ,
            ])
            ->count();
    }

    public static function delByModuleIdAndUserIdAndIds(int $module_id , int $user_id , array $ids)
    {
        return self::where([
            ['module_id' , '=' , $module_id] ,
            ['user_id' , '=' , $user_id] ,
        ])
            ->whereIn('id' , $ids)
            ->delete();
    }
}
