<?php


namespace App\Customize\api\web\action;

use App\Customize\api\web\handler\HistoryHandler;
use App\Customize\api\web\model\HistoryModel;
use App\Customize\api\web\model\ImageModel;
use App\Customize\api\web\model\ImageProjectModel;
use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\VideoModel;
use App\Customize\api\web\model\VideoProjectModel;
use App\Customize\api\web\repository\Repository;
use App\Http\Controllers\api\web\Base;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function api\web\my_config;
use function api\web\my_config_keys;
use function api\web\user;

class HistoryAction extends Action
{

    public static function less(Base $context , array $param = [])
    {
        $validator = Validator::make($param , [
            'module_id'             => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        $size = $param['size'] ? $param['size'] : my_config('app.limit');
        $res = HistoryModel::getOrderTimeByModuleIdAndUserIdAndSize($module->id , user()->id , $size);
        $res = HistoryHandler::handleAll($res);
        $groups = Repository::groupViaDateByModuleAndTypeAndCollectionSupportHistoryAndPraise($module , 'history' , $res);
        return self::success('' , $groups);
    }

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
        $user = user();
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = HistoryModel::getByModuleIdAndUserIdAndRelationTypeAndValueAndSize($module->id , $user->id , $param['relation_type'] , $param['value'] ,$size);
        $res = HistoryHandler::handlePaginator($res);
        $res->data = Repository::groupViaDateByModuleAndTypeAndCollectionSupportHistoryAndPraise($module , 'history' , $res->data);
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
        $histories = HistoryModel::getByModuleIdAndUserIdAndIds($module->id , $user->id , $ids);
        if (count($ids) !== count($histories)) {
            return self::error('存在无效记录，请重新选择');
        }
        // 检查记录是否是当前登录用户
        $count = HistoryModel::destroy($ids);
        return self::success('操作成功' , $count);
    }

    public static function store(Base $context , array $param = [])
    {
        $relation_type_range = my_config_keys('business.content_type');
        $validator = Validator::make($param , [
            'module_id'             => 'required|integer' ,
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
        switch ($param['relation_type'])
        {
            case 'image_project':
                $relation = ImageProjectModel::find($param['relation_id']);
                break;
            case 'video_project':
                $relation = VideoProjectModel::find($param['relation_id']);
                break;
            case 'image':
                $relation = ImageModel::find($param['relation_id']);
                break;
            case 'video':
                $relation = VideoModel::find($param['relation_id']);
                break;
        }
        if (empty($relation)) {
            return self::error('关联事物不存在' , '' , 404);
        }
        if ($module->id !== $relation->module_id) {
            return self::error('禁止记录不同模块的内容' , '' , 403);
        }
        $user = user();
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $res = HistoryModel::updateOrInsert([
            'module_id' => $module->id ,
            'user_id' => $user->id ,
            'relation_type' => $param['relation_type'] ,
            'relation_id' => $relation->id ,
            'date' => date('Y-m-d') ,
        ] , [
            'time' => date('H:i:s') ,
            'created_at' => $date . ' ' . $time ,
        ]);
        return self::success('操作成功');
    }
}
