<?php


namespace App\Customize\api\web\model;


use Illuminate\Database\Eloquent\Collection;

class NavModel extends Model
{
    protected $table = 'xq_nav';

    public function parent()
    {
        $this->hasOne(NavModel::class , 'id' , 'p_id');
    }

    public static function getAllByRelationAndModuleId(array $relation , int $module_id): Collection
    {
        return self::with($relation)
            ->where([
                ['module_id' , '=' , $module_id] ,
                ['is_enabled' , '=' , 1] ,
            ])
            ->orderBy('weight' , 'desc')
            ->orderBy('id' , 'asc')
            ->get();
    }

}
