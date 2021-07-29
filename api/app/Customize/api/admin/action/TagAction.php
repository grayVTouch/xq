<?php


namespace App\Customize\api\admin\action;

use App\Customize\api\admin\handler\TagHandler;
use App\Customize\api\admin\model\ModuleModel;
use App\Customize\api\admin\model\TagModel;
use App\Customize\api\admin\model\UserModel;
use App\Http\Controllers\api\admin\Base;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function api\admin\get_form_error;
use function api\admin\my_config;
use function api\admin\my_config_keys;
use function api\admin\parse_order;
use function core\array_unit;
use function core\current_date;

class TagAction extends Action
{
    public static function index(Base $context , array $param = [])
    {
        $order = $param['order'] === '' ? [] : parse_order($param['order'] , '|');
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = TagModel::index([
            'module' ,
            'user' ,
        ] , $param , $order , $size);
        $res = TagHandler::handlePaginator($res);
        foreach ($res->data as $v)
        {
            // 附加：模块
            TagHandler::module($v);
            // 附加：用户
            TagHandler::user($v);
        }
        return self::success('' , $res);
    }

    public static function update(Base $context , $id , array $param = [])
    {
        $type_range = my_config_keys('business.content_type');
        $status_range = my_config_keys('business.status_for_tag');
        $validator = Validator::make($param , [
            'name'      => 'required' ,
            'type' => ['required' , Rule::in($type_range)] ,
            'module_id' => 'required|integer' ,
            'user_id'   => 'required|integer' ,
            'status'    => ['required' , Rule::in($status_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $res = TagModel::find($id);
        if (empty($res)) {
            return self::error('标签不存在' , '' , 404);
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $tag = TagModel::findByExcludeIdAndModuleIdAndNameAndType($res->id , $module->id , $param['name'] , $param['type']);
        if (!empty($tag)) {
            return self::error('标签已经存在');
        }
        $user = UserModel::find($param['user_id']);
        if (empty($user)) {
            return self::error('用户不存在');
        }
        if ($param['status'] !== '' && $param['status'] == -1 && $param['fail_reason'] === '') {
            return self::error('请提供失败原因');
        }
        $datetime               = current_date();
        $param['weight']        = $param['weight'] === '' ? 0 : $param['weight'];
        $param['updated_at']    = $datetime;
        TagModel::updateById($res->id , array_unit($param , [
            'name' ,
            'type' ,
            'weight' ,
            'module_id' ,
            'user_id' ,
            'status' ,
            'fail_reason' ,
            'updated_at' ,
        ]));
        return self::success('操作成功');
    }

    public static function store(Base $context , array $param = [])
    {
        $type_range = my_config_keys('business.content_type');
        $status_range = my_config_keys('business.status_for_tag');
        $validator = Validator::make($param , [
            'name'      => 'required' ,
            'type' => ['required' , Rule::in($type_range)] ,
            'module_id' => 'required|integer' ,
            'user_id'   => 'required|integer' ,
            'status'    => ['required' , Rule::in($status_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $tag = TagModel::findByModuleIdAndNameAndType($module->id , $param['name'] , $param['type']);
        if (!empty($tag)) {
            return self::error('标签已经存在');
        }
        $user = UserModel::find($param['user_id']);
        if (empty($user)) {
            return self::error('用户不存在');
        }
        if ($param['status'] !== '' && $param['status'] == -1 && $param['fail_reason'] === '') {
            return self::error('请提供失败原因');
        }
        $datetime               = current_date();
        $param['weight']        = $param['weight'] === '' ? 0 : $param['weight'];
        $param['updated_at']    = $datetime;
        $param['created_at']    = $datetime;
        $id = TagModel::insertGetId(array_unit($param , [
            'name' ,
            'type' ,
            'weight' ,
            'module_id' ,
            'user_id' ,
            'status' ,
            'fail_reason' ,
            'updated_at' ,
            'created_at' ,
        ]));
        return self::success('' , $id);
    }

    public static function findOrCreate(Base $context , array $param = [])
    {
        $type_range = my_config_keys('business.content_type');
        $validator = Validator::make($param , [
            'name'      => 'required' ,
            'type' => ['required' , Rule::in($type_range)] ,
            'module_id' => 'required|integer' ,
            'user_id'   => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $tag = TagModel::findByModuleIdAndNameAndType($module->id , $param['name'] , $param['type']);
        if (!empty($tag)) {
            return self::success('' , $tag);
        }
        $user = UserModel::find($param['user_id']);
        if (empty($user)) {
            return self::error('用户不存在');
        }
        $datetime               = current_date();
        $param['status']        = 1;
        $param['updated_at']    = $datetime;
        $param['created_at']    = $datetime;
        $id = TagModel::insertGetId(array_unit($param , [
            'name' ,
            'type' ,
            'module_id' ,
            'user_id' ,
            'status' ,
            'updated_at' ,
            'created_at' ,
        ]));
        $tag = TagModel::find($id);
        TagHandler::handle($tag);
        TagHandler::module($tag);
        TagHandler::user($tag);
        return self::success('' , $tag);
    }


    public static function show(Base $context , $id , array $param = [])
    {
        $res = TagModel::find($id);
        if (empty($res)) {
            return self::error('标签不存在' , '' , 404);
        }
        $res = TagHandler::handle($res);

        // 附加：模块
        TagHandler::module($res);
        // 附加：用户
        TagHandler::user($res);

        return self::success('' , $res);
    }

    public static function destroy(Base $context , $id , array $param = [])
    {
        $count = TagModel::destroy($id);
        return self::success('操作成功' , $count);
    }

    public static function destroyAll(Base $context , array $ids , array $param = [])
    {
        $count = TagModel::destroy($ids);
        return self::success('操作成功' , $count);
    }

    public static function search(Base $context , $value , array $param = [])
    {
        if (empty($value)) {
            return self::error('请提供搜索值');
        }
        $res = TagModel::search($value);
        $res = TagHandler::handleAll($res);
        return self::success('' , $res);
    }

    public static function top(Base $context ,  array $param = [])
    {
        $type_range = my_config_keys('business.content_type');
        $validator = Validator::make($param , [
            'module_id' => 'required' ,
            'type' => ['required' , Rule::in($type_range)] ,
            'size' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块未找到' , '' , 404);
        }
        $size = empty($param['size']) ? $param['size'] : 10;
        $res = TagModel::topByModuleIdAndTypeAndSize($module->id , $param['type'] , $size);
        $res = TagHandler::handleAll($res);
        return self::success('' , $res);
    }
}
