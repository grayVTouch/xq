<?php


namespace App\Customize\api\web\action;

use App\Customize\api\web\handler\HistoryHandler;
use App\Customize\api\web\handler\ImageHandler;
use App\Customize\api\web\handler\ImageProjectHandler;
use App\Customize\api\web\handler\UserVideoPlayRecordHandler;
use App\Customize\api\web\handler\UserVideoProjectPlayRecordHandler;
use App\Customize\api\web\handler\VideoHandler;
use App\Customize\api\web\handler\VideoProjectHandler;
use App\Customize\api\web\model\HistoryModel;
use App\Customize\api\web\model\ImageModel;
use App\Customize\api\web\model\ImageProjectModel;
use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\VideoModel;
use App\Customize\api\web\model\VideoProjectModel;
use App\Http\Controllers\api\web\Base;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function api\web\my_config;
use function api\web\my_config_keys;
use function api\web\user;

class HistoryAction extends Action
{

    public static function lessHistory(Base $context , array $param = [])
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
        $user = user();
        $size = $param['size'] ? $param['size'] : my_config('app.limit');
        $res = HistoryModel::getOrderTimeByModuleIdAndUserIdAndSize($module->id , $user->id , $size);
        $res = HistoryHandler::handleAll($res);
        $date = date('Y-m-d');
        $yesterday = date_create('yesterday')->format('Y-m-d');
        $groups = [];
        $findIndex = function($name) use(&$groups): int
        {
            foreach ($groups as $k => $v)
            {
                if ($v['name'] === $name) {
                    return $k;
                }
            }
            return -1;
        };
        foreach ($res as $v)
        {
            // 附加：关联对象
            HistoryHandler::relation($v);
            switch ($v->relation_type)
            {
                case 'image_project':
                    ImageProjectHandler::user($v->relation);
                    break;
                case 'video_project':
                    VideoProjectHandler::user($v->relation);
                    // 记录历史
                    VideoProjectHandler::userPlayRecord($v->relation);
                    if (!empty($v->relation)) {
                        UserVideoProjectPlayRecordHandler::video($v->relation->user_play_record);
                    }
                    break;
                case 'image':
                    ImageHandler::user($v->relation);
                    break;
                case 'video':
                    VideoHandler::user($v->relation);
                    // 记录历史
                    VideoHandler::userPlayRecord($v->relation);
                    if (!empty($v->relation)) {
                        UserVideoPlayRecordHandler::video($v->relation->user_play_record);
                    }
                    break;
            }
            switch ($v->date)
            {
                case $date:
                    $name = '今天';
                    break;
                case $yesterday:
                    $name = '昨天';
                    break;
                default:
                    $name = $v->date;
            }
            $index = $findIndex($name);
            if ($index < 0) {
                $groups[] = [
                    'name' => $name ,
                    'data' => [] ,
                ];
                $index = count($groups) - 1;
            }
            $groups[$index]['data'][] = $v;
        }
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
        // 对时间进行分组
        $date = date('Y-m-d');
        $yesterday = date_create('yesterday')->format('Y-m-d');
        $groups = [];
        $findIndex = function($name) use(&$groups): int
        {
            foreach ($groups as $k => $v)
            {
                if ($v['name'] === $name) {
                    return $k;
                }
            }
            return -1;
        };
        foreach ($res->data as $v)
        {
            // 附加：关联对象
            HistoryHandler::relation($v);
            // 附加：用户
            switch ($v->relation_type)
            {
                case 'image_project':
                    ImageProjectHandler::user($v->relation);
                    break;
                case 'video_project':
                    VideoProjectHandler::user($v->relation);
                    // 记录历史
                    VideoProjectHandler::userPlayRecord($v->relation);
                    if (!empty($v->relation)) {
                        UserVideoProjectPlayRecordHandler::video($v->relation->user_play_record);
                    }
                    break;
                case 'image':
                    ImageHandler::user($v->relation);
                    break;
                case 'video':
                    VideoHandler::user($v->relation);
                    // 记录历史
                    VideoHandler::userPlayRecord($v->relation);
                    if (!empty($v->relation)) {
                        UserVideoPlayRecordHandler::video($v->relation->user_play_record);
                    }
                    break;
            }

            switch ($v->date)
            {
                case $date:
                    $name = '今天';
                    break;
                case $yesterday:
                    $name = '昨天';
                    break;
                default:
                    $name = $v->date;
            }
            $index = $findIndex($name);
            if ($index < 0) {
                $groups[] = [
                    'name' => $name ,
                    'data' => [] ,
                ];
                $index = count($groups) - 1;
            }
            $groups[$index]['data'][] = $v;
        }
        $res->data = $groups;
        return self::success('' , $res);
    }

    public static function destroyAll(Base $context , array $ids , array $param = []): array
    {
        $validator = Validator::make($param , [
            'module_id' => 'required' ,
            'history_ids'      => 'required' ,
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
        return self::success('' , $res);
    }
}
