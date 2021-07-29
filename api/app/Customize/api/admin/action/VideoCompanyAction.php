<?php


namespace App\Customize\api\admin\action;

use App\Customize\api\admin\handler\VideoCompanyHandler;
use App\Customize\api\admin\model\ModuleModel;
use App\Customize\api\admin\model\RegionModel;
use App\Customize\api\admin\model\UserModel;
use App\Customize\api\admin\model\VideoCompanyModel;
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

class VideoCompanyAction extends Action
{
    public static function index(Base $context , array $param = [])
    {
        $order = $param['order'] === '' ? [] : parse_order($param['order'] , '|');
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = VideoCompanyModel::index([
            'module' ,
            'user' ,
            'region' ,
        ] , $param , $order , $size);
        $res = VideoCompanyHandler::handlePaginator($res);
        foreach ($res->data as $v)
        {
            // 附加：模块
            VideoCompanyHandler::module($v);
            // 附加：用户
            VideoCompanyHandler::user($v);
            // 附加：主体
            VideoCompanyHandler::region($v);
        }
        return self::success('' , $res);
    }

    public static function update(Base $context , $id , array $param = [])
    {
        $status_range = my_config_keys('business.status_for_video_company');
        $validator = Validator::make($param , [
            'name'          => 'required' ,
            'module_id'     => 'required|integer' ,
            'country_id'    => 'sometimes|integer' ,
            'weight'        => 'sometimes|integer' ,
            'user_id'       => 'required|integer' ,
            'status'        => ['required' , Rule::in($status_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $res = VideoCompanyModel::find($id);
        if (empty($res)) {
            return self::error('记录不存在' , '' , 404);
        }
        if (VideoCompanyModel::findByNameAndExcludeId($param['name'] , $res->id)) {
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
        $country = RegionModel::find($param['country_id']);
        if (empty($country)) {
            return self::error('国家不存在');
        }
        $param['weight']        = $param['weight'] === '' ? 0 : $param['weight'];
        $param['country']       = $country->name;
        $param['updated_at']    = current_datetime();
        try {
            DB::beginTransaction();
            VideoCompanyModel::updateById($res->id , array_unit($param , [
                'name' ,
                'thumb' ,
                'description' ,
                'country_id' ,
                'country' ,
                'weight' ,
                'module_id' ,
                'user_id' ,
                'status' ,
                'fail_reason' ,
                'updated_at' ,
            ]));
            ResourceRepository::used($param['thumb']);
            if ($res->thumb !== $param['thumb']) {
                ResourceRepository::delete($res->thumb);
            }
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function store(Base $context , array $param = [])
    {
        $status_range = my_config_keys('business.status_for_video_company');
        $validator = Validator::make($param , [
            'name'          => 'required' ,
            'module_id'     => 'required|integer' ,
            'country_id'    => 'sometimes|integer' ,
            'weight'        => 'sometimes|integer' ,
            'user_id'       => 'required|integer' ,
            'status'        => ['required' , Rule::in($status_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        if (VideoCompanyModel::findByName($param['name'])) {
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
        $country = RegionModel::find($param['country_id']);
        if (empty($country)) {
            return self::error('国家不存在');
        }
        $datetime               = current_datetime();
        $param['weight']        = $param['weight'] === '' ? 0 : $param['weight'];
        $param['country']       = $country->name;
        $param['updated_at']    = $datetime;
        $param['created_at']    = $datetime;

        try {
            DB::beginTransaction();
            $id = VideoCompanyModel::insertGetId(array_unit($param , [
                'name' ,
                'thumb' ,
                'description' ,
                'country_id' ,
                'country' ,
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
        $res = VideoCompanyModel::find($id);
        if (empty($res)) {
            return self::error('记录不存在' , '' , 404);
        }
        $res = VideoCompanyHandler::handle($res);

        // 附加：模块
        VideoCompanyHandler::module($res);
        // 附加：用户
        VideoCompanyHandler::user($res);
        // 附加：地区
        VideoCompanyHandler::region($res);

        return self::success('' , $res);
    }

    public static function destroy(Base $context , $id , array $param = [])
    {
        $res = VideoCompanyModel::find($id);
        if (empty($res)) {
            return self::error('记录不存在' , '' , 404);
        }
        try {
            DB::beginTransaction();
            ResourceRepository::delete($res->thumb);
            $count = VideoCompanyModel::destroy($res->id);
            DB::commit();
            return self::success('操作成功' , $count);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function destroyAll(Base $context , array $ids , array $param = [])
    {
        $res = VideoCompanyModel::find($ids);
        try {
            DB::beginTransaction();
            foreach ($res as $v)
            {
                ResourceRepository::delete($v->thumb);
            }
            $count = VideoCompanyModel::destroy($ids);
            DB::commit();
            return self::success('操作成功' , $count);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function search(Base $context , array $param = [])
    {
        if (empty($param['module_id'])) {
            return self::error('请提供 module_id');
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = VideoCompanyModel::search($param['module_id'] , $param['value'] , $size);
        $res = VideoCompanyHandler::handlePaginator($res);
        return self::success('' , $res);
    }
}
