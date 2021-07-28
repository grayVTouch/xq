<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\DiskModel;
use App\Customize\api\web\model\Model;
use stdClass;
use function core\convert_object;
use function core\format_capacity;

class DiskHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        $total_size = format_capacity(disk_total_space($model->path) , 2);
        $free_size = format_capacity(disk_free_space($model->path) , 2);

        $model->total_size = $total_size;
        $model->free_size = $free_size;

        return $model;
    }
}
