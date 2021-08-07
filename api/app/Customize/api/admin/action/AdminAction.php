<?php


namespace App\Customize\api\admin\action;



use App\Customize\api\admin\handler\AdminHandler;
use App\Customize\api\admin\model\AdminModel;
use App\Customize\api\admin\model\DiskModel;
use App\Customize\api\admin\model\SystemSettingsModel;
use App\Customize\api\admin\model\WebRouteMappingModel;
use App\Customize\api\admin\repository\ResourceRepository;
use App\Http\Controllers\api\admin\Base;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function api\admin\my_config;
use function api\admin\my_config_keys;
use function api\admin\parse_order;
use function api\admin\user;
use function core\array_unit;
use function core\object_to_array;

class AdminAction extends Action
{
    public static function info(Base $context , array $param = []): array
    {
        $disk_count = DiskModel::countByIsDefault();
        $settings = SystemSettingsModel::first();
        return self::success('' , [
            'user' => user() ,
            'settings' => array_merge(object_to_array($settings) , [
                'is_init_disk' => $disk_count > 0 ? 1 : 0 ,
                'show_for_image_project_at_web' => WebRouteMappingModel::findBySymbol('ImageProject::show')->url ,
                'show_for_video_project_at_web' => WebRouteMappingModel::findBySymbol('VideoProject::show')->url ,
                'show_for_video_at_web' => WebRouteMappingModel::findBySymbol('Video::show')->url ,
                'show_for_image_at_web' => WebRouteMappingModel::findBySymbol('Image::show')->url ,
            ]) ,
        ]);
    }

    public static function search(Base $context , $value , array $param = [])
    {
        if (empty($value)) {
            return self::error('请提供搜索值');
        }
        $res = AdminModel::search($value);
        $res = AdminHandler::handleAll($res);
        foreach ($res as $v)
        {
            // 附加：权限
            AdminHandler::permissions($v);
            // 附加：角色
            AdminHandler::role($v);
        }
        return self::success('' , $res);
    }

    public static function index(Base $context , array $param = [])
    {
        $order = $param['order'] === '' ? [] : parse_order($param['order'] , '|');
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = AdminModel::index([
            'role' ,
        ] , $param , $order , $size);
        $res = AdminHandler::handlePaginator($res);
        foreach ($res->data as $v)
        {
            // 附加：权限
//            AdminHandler::permissions($v);
            // 附加：角色
            AdminHandler::role($v);
        }
        return self::success('' , $res);
    }

    public static function update(Base $context , $id , array $param = [])
    {
        $sex_range = my_config_keys('business.sex');
        $validator = Validator::make($param , [
            'username'  => 'required|min:4' ,
            'sex'       => ['required' , Rule::in($sex_range)] ,
            'role_id'   => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        // 检查用户名是否已经存在
        $res = AdminModel::find($id);
        if (empty($res)) {
            return self::error('用户不存在' , '' , 404);
        }
        if ($res->username !== $param['username'] && AdminModel::findByUsername($param['username'])) {
            return self::error('用户名已经被使用');
        }
        if ($res->is_root && $res->id != user()->id) {
            // 超级管理员
            return self::error('超级管理员仅不允许他人操作！' , '' , 403);
        }
        $param['birthday']      = in_array($param['birthday'] , ['' , 'null']) ? null : $param['birthday'];
        $param['password']      = $param['password'] === '' ? $res->password : Hash::make($param['password']);
        try {
            DB::beginTransaction();
            AdminModel::updateById($res->id , array_unit($param , [
                'username' ,
                'password' ,
                'birthday' ,
                'avatar' ,
                'phone' ,
                'email' ,
                'role_id' ,
                'sex' ,
            ]));
            ResourceRepository::used($param['avatar']);
            if ($res->avatar !== $param['avatar']) {
                // 添加到待删除资源列表
                ResourceRepository::delete($res->avatar);
            }
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public static function store(Base $context , array $param = [])
    {
        $sex_range = my_config_keys('business.sex');
        $validator = Validator::make($param , [
            'username'  => 'required|min:4' ,
            'password'  => 'required' ,
            'sex'       => ['required' , Rule::in($sex_range)] ,
            'role_id'   => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $res = AdminModel::findByUsername($param['username']);
        if (!empty($res)) {
            return self::error('用户名已经被使用');
        }
        $param['birthday']      = in_array($param['birthday'] , ['' , 'null']) ? null : $param['birthday'];
        $param['password']      = Hash::make($param['password']);
        try {
            DB::beginTransaction();
            $id = AdminModel::insertGetId(array_unit($param , [
                'username' ,
                'password' ,
                'birthday' ,
                'avatar' ,
                'phone' ,
                'email' ,
                'role_id' ,
            ]));
            ResourceRepository::used($param['avatar']);
            DB::commit();
            return self::success('' , $id);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function show(Base $context , $id , array $param = [])
    {
        $res = AdminModel::find($id);
        if (empty($res)) {
            return self::error('用户不存在' , '' , 404);
        }
        $res = AdminHandler::handle($res);
        // 附加：角色
        AdminHandler::role($res);
        // 附加：权限
        AdminHandler::permissions($res);

        return self::success('' , $res);
    }

    public static function destroy(Base $context , $id , array $param = [])
    {
        $res = AdminModel::find($id);
        if (empty($res)) {
            return self::error('记录不存在' , '' , 404);
        }
        if ($res->is_root) {
            return self::error('禁止对超级管理员操作' , '' , 403);
        }
        try {
            DB::beginTransaction();
            $count = AdminModel::destroy($id);
            ResourceRepository::delete($res->avatar);
            DB::commit();
            return self::success('操作成功' , $count);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function destroyAll(Base $context , array $ids , array $param = [])
    {
        try {
            DB::beginTransaction();
            $res = AdminModel::find($ids);
            foreach ($res as $v)
            {
                if ($v->is_root) {
                    return self::error('包含超级管理员，禁止操作' , '' , 403);
                }
                ResourceRepository::delete($v->avatar);
                AdminModel::destroy($v->id);
            }
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
