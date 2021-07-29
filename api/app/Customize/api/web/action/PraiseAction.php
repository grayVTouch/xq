<?php


namespace App\Customize\api\web\action;

use App\Customize\api\web\handler\PraiseHandler;
use App\Customize\api\web\model\ImageProjectModel;
use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\PraiseModel;
use App\Customize\api\web\repository\Repository;
use App\Http\Controllers\api\web\Base;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function api\web\my_config;
use function api\web\my_config_keys;
use function api\web\user;

class PraiseAction extends Action
{

    public static function index(Base $context , array $param = []): array
    {
        $relation_type_range = my_config_keys('business.content_type');
        $validator = Validator::make($param , [
            'module_id'         => 'required|integer' ,
            'relation_type'     => ['sometimes' , Rule::in($relation_type_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = PraiseModel::getByModuleIdAndUserIdAndRelationTypeAndValueAndSize($module->id , user()->id , $param['relation_type'] , $param['value'] ,$size);
        $res = PraiseHandler::handlePaginator($res);
        $res->data = $groups = Repository::groupViaDateByModuleAndTypeAndCollectionSupportHistoryAndPraise($module , 'praise' , $res->data);;
        return self::success('' , $res);
    }

    public static function destroyAll(Base $context , array $ids , array $param = []): array
    {
        $validator = Validator::make($param , [
            'module_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        if (empty($ids)) {
            return self::error('请提供待删除的项');
        }
        $user = user();
        $histories = PraiseModel::getByModuleIdAndUserIdAndIds($module->id , $user->id , $ids);
        if (count($ids) !== count($histories)) {
            return self::error('存在无效记录，请重新选择');
        }
        // 检查记录是否是当前登录用户
        $count = PraiseModel::destroy($ids);
        return self::success('操作成功' , $count);
    }


    //
    public static function createOrCancel(Base $context , array $param = [])
    {
        $relation_type_range    = my_config_keys('business.relation_type_for_praise');
        $action_range           = my_config_keys('business.bool_for_int');
        $validator = Validator::make($param , [
            'module_id'             => 'required|integer' ,
            'action'                => ['required' , Rule::in($action_range)] ,
            'relation_type' => ['required' , Rule::in($relation_type_range)] ,
            'relation_id' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        if ($param['relation_type'] === 'image_project') {
            // 图片专题
            $relation = ImageProjectModel::find($param['relation_id']);
            if (empty($relation)) {
                return self::error('图片专题不存在');
            }
            $user = user();
            if ($param['action'] == 1) {
                // 点赞
                $praise = PraiseModel::findByModuleIdAndUserIdAndRelationTypeAndRelationId($module->id , $user->id , $param['relation_type'] , $relation->id);
                if (empty($praise)) {
                    PraiseModel::insertOrIgnore([
                        'module_id' => $module->id ,
                        'user_id' => $user->id ,
                        'relation_type' => $param['relation_type'] ,
                        'relation_id' => $relation->id ,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
                ImageProjectModel::incrementByIdAndColumnAndStep($relation->id , 'praise_count' , 1);
            } else {
                // 取消收藏
                PraiseModel::delByModuleIdAndUserIdAndRelationTypeAndRelationId($module->id , $user->id , 'image_project' , $relation->id);
                ImageProjectModel::decrementByIdAndColumnAndStep($relation->id , 'praise_count' , 1);
            }
        } else {
            // 其他类型，预留
        }
        return self::success('操作成功');
    }

}
