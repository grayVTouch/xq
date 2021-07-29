<?php


namespace App\Customize\api\web\model;



use Illuminate\Contracts\Pagination\Paginator;

class HistoryModel extends Model
{
    protected $table = 'xq_history';

    // 获取给定数量的历史记录（按照时间倒叙排序）
    public static function getOrderTimeByModuleIdAndUserIdAndSize(int $module_id , int $user_id , int $size = 20)
    {
        return self::where([
                ['module_id' , '=' , $module_id] ,
                ['user_id' , '=' , $user_id] ,
            ])
            ->orderBy('created_at' , 'desc')
            ->limit($size)
            ->get();
    }

    public static function getOrderTimeByModuleIdAndUserIdAndSizeUseRawGrammer(int $module_id , int $user_id , int $size = 20)
    {
        return self::where([
            ['module_id' , '=' , $module_id] ,
            ['user_id' , '=' , $user_id] ,
        ])
            ->orderBy('created_at' , 'desc')
            ->limit($size)
            ->get();
    }

    public static function getByModuleIdAndUserIdAndRelationTypeAndValueAndSize(int $module_id , int $user_id , string $relation_type = '' , string $value = '' , int $size = 20): Paginator
    {
        $where = [
            ['h.module_id' , '=' , $module_id] ,
            ['h.user_id' , '=' , $user_id] ,
        ];
        if (!empty($relation_type)) {
            $where[] = ['h.relation_type' , '=' , $relation_type];
        }
        $query = self::select('h.*')
            ->from('xq_history as h');

        $handle_image_project = function() use($value , $query){
            $query->leftJoin('xq_image_project as ip' , function($join){
                // $join->on 会把内容当成是字段
                // $join->where 仅把值当成是值
                $join->on('h.relation_id' , '=' , 'ip.id')
                    ->where('h.relation_type' , '=' , 'image_project');
            });
        };
        $handle_video_project = function() use($value , $query){
            $query->leftJoin('xq_video_project as vp' , function($join){
                $join->on('h.relation_id' , '=' , 'vp.id')
                    ->where('h.relation_type' , '=' , 'video_project');
            });
        };
        $handle_video = function() use($value , $query){
            $query->leftJoin('xq_video as v' , function($join){
                $join->on('h.relation_id' , '=' , 'v.id')
                    ->where('h.relation_type' , '=' , 'video');
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
            ->orderBy('h.created_at' , 'desc')
            ->paginate($size);
    }

    public static function findByModuleIdAndUserIdAndRealtionTableAndDate(int $module_id , int $user_id , string $relation_type , string $date)
    {
        return self::where([
                ['module_id' , '=' , $module_id] ,
                ['user_id' , '=' , $user_id] ,
                ['relation_type' , '=' , $relation_type] ,
                ['date' , '=' , $date] ,
            ])
            ->first();
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
