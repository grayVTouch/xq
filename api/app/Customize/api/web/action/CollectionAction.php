<?php


namespace App\Customize\api\web\action;

use App\Customize\api\web\model\CollectionModel;
use App\Customize\api\web\model\ModuleModel;
use App\Http\Controllers\api\web\Base;
use Illuminate\Support\Facades\Validator;
use function api\web\user;

class CollectionAction extends Action
{
    public static function destroy(Base $context , int $id , array $param = []): array
    {
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        $user = user();
        $res = CollectionModel::delByModuleIdAndUserIdAndId($module->id , $user->id , $id);
        return self::success('' , $res);
    }
}
