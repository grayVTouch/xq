<?php


namespace App\Customize\api\admin\action;

use App\Customize\api\admin\handler\VideoSeriesHandler;
use App\Customize\api\admin\handler\UserHandler;
use App\Customize\api\admin\model\ModuleModel;
use App\Customize\api\admin\model\VideoSeriesModel;
use App\Customize\api\admin\model\UserModel;
use App\Customize\api\admin\repository\ResourceRepository;
use App\Http\Controllers\api\admin\Base;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function api\admin\get_form_error;
use function api\admin\my_config;
use function api\admin\my_config_keys;
use function api\admin\parse_order;
use function core\array_unit;
use function core\current_datetime;

class VideoSeriesAction extends Action
{
    public static function index(Base $context , array $param = [])
    {
        $order = $param['order'] === '' ? [] : parse_order($param['order'] , '|');
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = VideoSeriesModel::index([
            'user' ,
            'module' ,
        ] , $param , $order , $size);
        $res = VideoSeriesHandler::handlePaginator($res);
        foreach ($res->data as $v)
        {
            // 附加：模块
            VideoSeriesHandler::module($v);
            // 附加：用户
            VideoSeriesHandler::user($v);
        }
        return self::success('' , $res);
    }

    public static function update(Base $context , $id , array $param = [])
    {
        $status_range = my_config_keys('business.status_for_video_series');
        $validator = Validator::make($param , [
            'name'      => 'required' ,
            'module_id' => 'required|integer' ,
            'weight'    => 'sometimes|integer' ,
            'user_id'   => 'required|integer' ,
            'status'    => ['required' , Rule::in($status_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $res = VideoSeriesModel::find($id);
        if (empty($res)) {
            return self::error('视频系列未找到' , '' , 404);
        }
        if (VideoSeriesModel::findByNameAndExcludeId($param['name'] , $res->id)) {
            return self::error('名称已经被使用');
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        $user = UserModel::find($param['user_id']);
        if (empty($user)) {
            return self::error('用户不存在');
        }
        if ($param['status'] !== '' && $param['status'] == -1 && $param['fail_reason'] === '') {
            return self::error('请提供失败原因');
        }
        $param['weight']        = $param['weight'] === '' ? 0 : $param['weight'];
        $param['updated_at']    = current_datetime();
        try {
            DB::beginTransaction();
            VideoSeriesModel::updateById($res->id , array_unit($param , [
                'name' ,
                'thumb' ,
                'weight' ,
                'module_id' ,
                'user_id' ,
                'status' ,
                'fail_reason' ,
                'updated_at' ,
            ]));
            if ($res->thumb !== $param['thumb']) {
                ResourceRepository::delete($res->thumb);
            }
            ResourceRepository::used($param['thumb']);
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function store(Base $context , array $param = [])
    {
        $status_range = my_config_keys('business.status_for_video_series');
        $validator = Validator::make($param , [
            'name'      => 'required' ,
            'weight'    => 'sometimes|integer' ,
            'module_id' => 'required|integer' ,
            'user_id'   => 'required|integer' ,
            'status'    => ['required' , Rule::in($status_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        if (VideoSeriesModel::findByName($param['name'])) {
            return self::error('名称已经被使用');
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        $user = UserModel::find($param['user_id']);
        if (empty($user)) {
            return self::error('用户不存在');
        }
        if ($param['status'] !== '' && $param['status'] == -1 && $param['fail_reason'] === '') {
            return self::error('请提供失败原因');
        }
        $datetime = current_datetime();
        $param['weight']        = $param['weight'] === '' ? 0 : $param['weight'];
        $param['updated_at']    = $datetime;
        $param['created_at']    = $datetime;
        try {
            $id = VideoSeriesModel::insertGetId(array_unit($param , [
                'name' ,
                'thumb' ,
                'weight' ,
                'module_id' ,
                'user_id' ,
                'status' ,
                'fail_reason' ,
                'updated_at' ,
                'created_at' ,
            ]));
            ResourceRepository::used($param['thumb']);
            DB::commit();
            return self::success('' , $id);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function show(Base $context , $id , array $param = [])
    {
        $res = VideoSeriesModel::find($id);
        if (empty($res)) {
            return self::error('记录不存在' , '' , 404);
        }
        $res = VideoSeriesHandler::handle($res);
        // 附加：模块
        VideoSeriesHandler::module($res);
        // 附加：用户
        VideoSeriesHandler::user($res);

        return self::success('' , $res);
    }

    public static function destroy(Base $context , $id , array $param = [])
    {
        $count = VideoSeriesModel::destroy($id);
        return self::success('操作成功' , $count);
    }

    public static function destroyAll(Base $context , array $ids , array $param = [])
    {
        $count = VideoSeriesModel::destroy($ids);
        return self::success('操作成功' , $count);
    }

    public static function search(Base $context , array $param = [])
    {
        if (empty($param['module_id'])) {
            return self::error('请提供 module_id');
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = VideoSeriesModel::search($param['module_id'] , $param['value'] , $size);
        $res = VideoSeriesHandler::handlePaginator($res);
        return self::success('' , $res);
    }
}
