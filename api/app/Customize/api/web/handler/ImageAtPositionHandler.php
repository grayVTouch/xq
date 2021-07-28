<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\PositionModel;
use App\Customize\api\web\model\Model;
use stdClass;
use function core\convert_object;

class ImageAtPositionHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $res = convert_object($model);

        return $res;
    }

    // 模块
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
