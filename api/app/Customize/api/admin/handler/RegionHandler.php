<?php


namespace App\Customize\api\admin\handler;


use App\Customize\api\admin\model\Model;
use App\Customize\api\admin\model\RegionModel;
use stdClass;
use function core\convert_object;

class RegionHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        return $model;
    }

    public static function region($model): void
    {
        if (empty($model)) {
            return ;
        }
        $region = $model->p_id ? RegionModel::find($model->p_id) : null;
        $region = self::handle($region);
        $model->region = $region;
    }

}
