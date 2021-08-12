<?php


namespace App\Customize\api\web\repository;


use App\Customize\api\web\model\ResourceModel;
use function core\current_datetime;

class ResourceRepository
{
    /**
     * 穿件资源
     *
     * @param string $url
     * @param string $path
     * @param string $disk
     * @param int $is_used
     * @param int $is_deleted
     * @return void
     */
    public static function create(string $url , string $path , string $disk , int $is_used = 0 , int $is_deleted = 0): void
    {
        if (empty($path)) {
            return ;
        }
        $datetime = current_datetime();

        $res = ResourceModel::findByUrlOrPath($url);
        if (empty($res)) {
            ResourceModel::insert([
                'url'           => $url ,
                'path'          => $path ,
                'disk'          => $disk ,
                'is_used'       => $is_used ,
                'is_deleted'    => $is_deleted ,
                'created_at'    => $datetime ,
            ]);
            return ;
        }
        ResourceModel::updateByUrlOrPath($url , [
            'path'          => $path ,
            'disk'          => $disk ,
            'is_used'       => $is_used ,
            'status'    => 0 ,
            'updated_at'    => $datetime ,
        ]);
    }

    /**
     * 删除资源
     *
     * @author running
     *
     * @param string $path
     * @return int|null
     */
    public static function delete(string $url): int
    {
        if (empty($url)) {
            return -1;
        }
        return ResourceModel::updateByUrlOrPath($url , [
            'is_deleted' => 1 ,
        ]);
    }

    public static function used(string $url): int
    {
        if (empty($url)) {
            return -1;
        }
        return ResourceModel::updateByUrlOrPath($url , [
            'is_used' => 1 ,
        ]);
    }
}
