<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\Model;
use App\Customize\api\web\model\RegionModel;
use stdClass;
use function core\convert_object;

class RegionHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $res = convert_object($model);

        return $res;
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
