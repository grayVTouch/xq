<?php


namespace App\Customize\api\web\model;


use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class PraiseModel extends Model
{
    protected $table = 'xq_praise';

    public static function findByModuleIdAndUserIdAndRelationTypeAndRelationId(int $module_id ,int $user_id , string $relation_type , int $relation_id): ?PraiseModel
    {
        return self::where([
                ['module_id' , '=' , $module_id] ,
                ['user_id' , '=' , $user_id] ,
                ['relation_type' , '=' , $relation_type] ,
                ['relation_id' , '=' , $relation_id] ,
            ])
            ->first();
    }

    public static function getByModuleIdAndUserIdAndRelationTypeAndRelationIds(int $module_id ,int $user_id , string $relation_type , array $relation_ids = []): ?Collection
    {
        return self::where([
                ['module_id' , '=' , $module_id] ,
                ['user_id' , '=' , $user_id] ,
                ['relation_type' , '=' , $relation_type] ,
            ])
            ->whereIn('relation_id' , $relation_ids)
            ->get();
    }

    public static function delByModuleIdAndUserIdAndRelationTypeAndRelationId(int $module_id ,int $user_id , string $relation_type , int $relation_id): int
    {
        return self::where([
            ['module_id' , '=' , $module_id] ,
            ['user_id' , '=' , $user_id] ,
            ['relation_type' , '=' , $relation_type] ,
            ['relation_id' , '=' , $relation_id] ,
        ])
            ->delete();
    }

    public static function countByUserId(int $user_id)
    {
        return self::where('user_id' , $user_id)
            ->count();
    }

    public static function getByModuleIdAndUserIdAndRelationTypeAndValueAndSize(int $module_id , int $user_id , string $relation_type = '' , string $value = '' , int $size = 20): Paginator
    {
        $where = [
            ['p.module_id' , '=' , $module_id] ,
            ['p.user_id' , '=' , $user_id] ,
        ];
        if (!empty($relation_type)) {
            $where[] = ['p.relation_type' , '=' , $relation_type];
        }
        $query = self::select('p.*')
            ->from('xq_praise as p');

        $handle_image_project = function() use($value , $query){
            $query->leftJoin('xq_image_project as ip' , function($join){
                // $join->on 会把内容当成是字段
                // $join->where 仅把值当成是值
                $join->on('p.relation_id' , '=' , 'ip.id')
                    ->where('p.relation_type' , '=' , 'image_project');
            });
        };
        $handle_video_project = function() use($value , $query){
            $query->leftJoin('xq_video_project as vp' , function($join){
                // $join->on 会把内容当成是字段
                // $join->where 仅把值当成是值
                $join->on('p.relation_id' , '=' , 'vp.id')
                    ->where('p.relation_type' , '=' , 'video_project');
            });
        };
        switch ($relation_type)
        {
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
            default:
                $handle_image_project();
                $handle_video_project();
                if (!empty($value)) {
                    $query->where(function($query) use($value){
                        $query->where('ip.name' , 'like' , "%{$value}%")
                            ->orWhere('vp.name' , 'like' , "%{$value}%");
                    });
                }
        }
        return $query->where($where)
            ->orderBy('p.created_at' , 'desc')
            ->paginate($size);
    }

    public static function getByModuleIdAndUserIdAndIds(int $module_id , int $user_id , array $ids = [])
    {
        return self::where([
            ['module_id' , '=' , $module_id] ,
            ['user_id' , '=' , $user_id] ,
        ])
            ->whereIn('id' , $ids)->get();
    }
}
