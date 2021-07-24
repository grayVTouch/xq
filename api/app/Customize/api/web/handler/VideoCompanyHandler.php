<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\RegionModel;
use App\Customize\api\web\model\VideoCompanyModel;
use App\Customize\api\web\repository\FileRepository;
use App\Customize\api\web\model\Model;
use stdClass;
use function core\convert_object;

class VideoCompanyHandler extends Handler
{
    public static function handle(?Model $model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $res = convert_object($model);

        $module = ModuleModel::find($res->module_id);
        ModuleHandler::handle($module);

        $region = RegionModel::find($res->country_id);
        $region = RegionHandler::handle($region);

        $res->module = $module;
        $res->region = $region;



        return $res;
    }

}
