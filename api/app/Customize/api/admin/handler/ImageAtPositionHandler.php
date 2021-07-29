<?php


namespace App\Customize\api\admin\handler;


use App\Customize\api\admin\model\ImageAtPositionModel;
use App\Customize\api\admin\model\ModuleModel;
use App\Customize\api\admin\model\PositionModel;
use App\Customize\api\admin\repository\FileRepository;
use App\Customize\api\admin\model\Model;
use stdClass;
use function api\admin\get_config_key_mapping_value;

use function core\convert_object;

class ImageAtPositionHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $res = convert_object($model);

        $res->__platform = get_config_key_mapping_value('business.platform' , $res->platform);

        return $res;
    }

    public static function module($model): void
    {
        if (empty($model)) {
            return ;
        }
        $module = ModuleModel::find($model->module_id);
        $module = ModuleHandler::handle($module);
        $model->module = $module;
    }

    public static function position($model): void
    {
        if (empty($model)) {
            return ;
        }
        $position = PositionModel::find($model->position_id);
        $position = PositionHandler::handle($position);

        $model->position = $position;
    }
}
