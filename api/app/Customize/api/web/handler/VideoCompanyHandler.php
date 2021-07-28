<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\RegionModel;
use stdClass;
use function core\convert_object;

class VideoCompanyHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $res = convert_object($model);

        return $res;
    }

    public static function country($model): void
    {
        if (empty($model)) {
            return ;
        }
        $country = property_exists($model , 'country') ? $model->user : RegionModel::find($model->country_id);
        $country = RegionHandler::handle($country);

        $model->country = $country;
    }


}
