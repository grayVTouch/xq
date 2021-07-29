<?php


namespace App\Customize\api\web\model;


use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class CollectionModel extends Model
{
    protected $table = 'xq_collection';

    public static function delByModuleIdAndUserIdAndCollectionGroupIdAndRelationTypeAndRelationId(int $module_id , int $user_id , int $collection_group_id , string $relation_type , int $relation_id): int
    {
        return self::where([
            ['module_id' , '=' , $module_id] ,
            ['user_id' , '=' , $user_id] ,
            ['collection_group_id' , '=' , $collection_group_id] ,
            ['relation_type' , '=' , $relation_type] ,
            ['relation_id' , '=' , $relation_id] ,
        ])->delete();
    }

    public static function delByModuleIdAndUserIdAndId(int $module_id , int $user_id , int $id)
    {
        return self::where([
            ['module_id' , '=' , $module_id] ,
            ['user_id' , '=' , $user_id] ,
            ['id' , '=' , $id] ,
        ])->delete();
    }

    public static function delByModuleIdAndUserIdAndCollectionGroupIds(int $module_id , int $user_id , array $collection_group_ids)
    {
        return self::where([
                ['module_id' , '=' , $module_id] ,
                ['user_id' , '=' , $user_id] ,
            ])
            ->whereIn('collection_group_id' , $collection_group_ids)
            ->delete();
    }

    public static function findByModuleIdAndUserIdAndRelationTypeAndRelationId(int $module_id , int $user_id , string $relation_type , int $relation_id): ?CollectionModel
    {
        return self::where([
            ['module_id' , '=' , $module_id] ,
            ['user_id' , '=' , $user_id] ,
            ['relation_type' , '=' , $relation_type] ,
            ['relation_id' , '=' , $relation_id] ,
        ])->first();
    }

    public static function countByModuleIdAndUserIdAndCollectionGroupId(int $module_id , int $user_id , int $collection_group_id): int
    {
        return self::where([
            ['module_id' , '=' , $module_id] ,
            ['user_id' , '=' , $user_id] ,
            ['collection_group_id' , '=' , $collection_group_id] ,
        ])->count();
    }

    public static function countByCollectionGroupId(int $collection_group_id): int
    {
        return self::where([
            ['collection_group_id' , '=' , $collection_group_id] ,
        ])->count();
    }

    public static function countByCollectionGroupIdAndRelationType(int $collection_group_id , string $relation_type): int
    {
        return self::where([
            ['collection_group_id' , '=' , $collection_group_id] ,
            ['relation_type' , '=' , $relation_type] ,
        ])->count();
    }

    public static function findByModuleIdAndUserIdAndCollectionGroupIdAndRelationTypeAndRelationId(int $module_id , int $user_id ,int $collection_group_id , string $relation_type , int $relation_id): ?CollectionModel
    {
        return self::where([
            ['module_id' , '=' , $module_id] ,
            ['user_id' , '=' , $user_id] ,
            ['collection_group_id' , '=' , $collection_group_id] ,
            ['relation_type' , '=' , $relation_type] ,
            ['relation_id' , '=' , $relation_id] ,
        ])->first();
    }

    public static function countByModuleIdAndRelationTypeAndRelationId(int $module_id , string $relation_type , int $relation_id): int
    {
        return self::where([
            ['module_id' , '=' , $module_id] ,
            ['relation_type' , '=' , $relation_type] ,
            ['relation_id' , '=' , $relation_id] ,
        ])->count();
    }

    public static function getByModuleIdAndUserIdAndCollectionGroupIdAndSize(int $module_id , string $user_id , int $collection_group_id , int $size = 20): Collection
    {
        return self::where([
                ['module_id' , '=' , $module_id] ,
                ['user_id' , '=' , $user_id] ,
                ['collection_group_id' , '=' , $collection_group_id] ,
            ])
            ->orderBy('created_at' , 'desc')
            ->limit($size)
            ->get();
    }

    public static function getWithPagerByModuleIdAndUserIdAndCollectionGroupIdAndSize(int $module_id , int $user_id , int $collection_group_id , string $relation_type = '' , int $size = 20): Paginator
    {
        $where = [
            ['module_id' , '=' , $module_id] ,
            ['user_id' , '=' , $user_id] ,
            ['collection_group_id' , '=' , $collection_group_id] ,
        ];
        if (!empty($relation_type)) {
            $where[] = ['relation_type' , '=' , $relation_type];
        }
        return self::where($where)
            ->orderBy('created_at' , 'desc')
            ->paginate($size);
    }

    public static function getWithPagerByModuleIdAndUserIdAndCollectionGroupIdAndValueAndRelationTypeAndSize(int $module_id , int $user_id , int $collection_group_id , string $value = '' , string $relation_type = '' , int $size = 20): Paginator
    {
        $where = [
            ['c.module_id' , '=' , $module_id] ,
            ['c.user_id' , '=' , $user_id] ,
            ['c.collection_group_id' , '=' , $collection_group_id] ,
        ];
        if (!empty($relation_type)) {
            $where[] = ['c.relation_type' , '=' , $relation_type];
        }
        $query = self::select('c.*')
            ->from('xq_collection as c');

        $handle_image_project = function() use($value , $query){
            $query->leftJoin('xq_image_project as ip' , function($join){
                // $join->on 会把内容当成是字段
                // $join->where 仅把值当成是值
                $join->on('c.relation_id' , '=' , 'ip.id')
                    ->where('c.relation_type' , '=' , 'image_project');
            });
        };
        $handle_video_project = function() use($value , $query){
            $query->leftJoin('xq_video_project as vp' , function($join){
                // $join->on 会把内容当成是字段
                // $join->where 仅把值当成是值
                $join->on('c.relation_id' , '=' , 'vp.id')
                    ->where('c.relation_type' , '=' , 'video_project');
            });
        };
        $handle_video = function() use($value , $query){
            $query->leftJoin('xq_video as v' , function($join){
                // $join->on 会把内容当成是字段
                // $join->where 仅把值当成是值
                $join->on('c.relation_id' , '=' , 'v.id')
                    ->where('c.relation_type' , '=' , 'video');
            });
        };
        switch ($relation_type)
        {
            case 'image':
            case 'image_project':
                if (!empty($value)) {
                    $where[] = ['ip.name' , 'like' , "%{$value}%"];
                }
                $handle_image_project();
                break;
            case 'video_project':
                if (!empty($value)) {
                    $where[] = ['vp.name' , 'like' , "%{$value}%"];
                }
                $handle_video_project();
                break;
            case 'video':
                if (!empty($value)) {
                    $where[] = ['v.name' , 'like' , "%{$value}%"];
                }
                $handle_video();
                break;
            default:
                $handle_image_project();
                $handle_video_project();
                $handle_video();
                if (!empty($value)) {
                    $query->where(function($query) use($value){
                        $query->where('ip.name' , 'like' , "%{$value}%")
                            ->orWhere('vp.name' , 'like' , "%{$value}%")
                            ->orWhere('v.name' , 'like' , "%{$value}%");
                    });
                }
        }

        return $query->where($where)
            ->orderBy('c.created_at' , 'desc')
            ->paginate($size);
    }

    public static function countByUserId(int $user_id)
    {
        return self::where('user_id' , $user_id)->count();
    }

    public static function firstOrderIdAscByCollectionGroupId(int $collection_group_id): ?CollectionModel
    {
        return self::where('collection_group_id' , $collection_group_id)
            ->orderBy('id' , 'asc')
            ->first();
    }
}
