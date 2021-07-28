<?php


namespace App\Customize\api\web\action;

use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\handler\ImageAtPositionHandler;
use App\Customize\api\web\model\ImageAtPositionModel;
use App\Customize\api\web\model\PositionModel;
use App\Http\Controllers\api\web\Base;
use Illuminate\Support\Facades\Validator;

class ImageAtPositionAction extends Action
{
    public static function imageAtPosition(Base $context , string $position ,  array $param = [])
    {
        $validator = Validator::make($param , [
            'module_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $position = PositionModel::getByPlatformAndValue('web' , $position);
        if (empty($position)) {
            return self::error('位置不存在' , '' , 404);
        }
        $res = ImageAtPositionModel::getByModuleIdAndPositionId($module->id , $position->id);
        $res = ImageAtPositionHandler::handleAll($res);
        return self::success('' , $res);
    }
}
