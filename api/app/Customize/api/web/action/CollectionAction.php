<?php


namespace App\Customize\api\web\action;


use App\Customize\api\web\handler\CollectionGroupHandler;
use App\Customize\api\web\model\CollectionGroupModel;
use App\Customize\api\web\model\CollectionModel;
use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\UserModel;
use App\Http\Controllers\api\web\Base;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function api\web\my_config_keys;
use function api\web\user;

class CollectionAction extends Action
{
    public static function index(Base $context , array $param = []): array
    {
        $validator = Validator::make($param , [
            'user_id' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $user = UserModel::find($param['user_id']);
        if (empty($user)) {
            return self::error('用户不存在' , '' , 404);
        }
        $relation_type_range = my_config_keys('business.content_type');
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
            'relation_type' => ['sometimes' , Rule::in($relation_type_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        $res = CollectionGroupModel::getByModuleIdAndUserIdAndRelationTypeAndValue($module->id , $user->id , $param['relation_type'] ,  $param['value']);
        $res = CollectionGroupHandler::handleAll($res);
        foreach ($res as $v)
        {
            // 附加：累计数量
            CollectionGroupHandler::count($v);
            // 附加：封面
            CollectionGroupHandler::thumb($v);
            // 附加：图片专题数量
            CollectionGroupHandler::countForImageProject($v);
        }
        return self::success('' , $res);
    }

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
