<?php


namespace App\Customize\api\admin\model;


use Illuminate\Database\Eloquent\Collection;

class ImageModel extends Model
{
    protected $table = 'xq_image';

    public static function delByImageProjectId(int $image_project_id): int
    {
        return self::where('image_project_id' , $image_project_id)->delete();
    }

    public static function getByImageProjectId(int $image_project_id): Collection
    {
        return self::where('image_project_id' , $image_project_id)->get();
    }

    public static function updateByImageProjectId(int $image_project_id , array $data): int
    {
        return self::where('image_project_id' , $image_project_id)->update($data);
    }
}
