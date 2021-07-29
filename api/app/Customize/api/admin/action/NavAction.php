<?php


namespace App\Customize\api\admin\action;



use App\Customize\api\admin\handler\NavHandler;
use App\Customize\api\admin\model\NavModel;
use App\Http\Controllers\api\admin\Base;
use Core\Lib\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function api\admin\get_form_error;
use function api\admin\my_config;
use function api\admin\my_config_keys;
use function core\array_unit;
use function core\current_datetime;
use function core\object_to_array;

class NavAction extends Action
{
    public static function index(Base $context , array $param = [])
    {
        $res = NavModel::getByRelationAndFilter([
            'module' ,
            'category' ,
            'parent' ,
        ] , $param);
        $res = NavHandler::handleAll($res);
        foreach ($res as $v)
        {
            // 附加：模块
            NavHandler::module($v);
            // 附加：上级项
            NavHandler::parent($v , false);
            // 附加：分类
            NavHandler::category($v);
        }
        $res = object_to_array($res);
        $res = Category::childrens(0 , $res , [
            'id'    => 'id' ,
            'p_id'  => 'p_id' ,
        ] , false , false);
        return self::success('' , $res);
    }

    public static function search(Base $context , array $param = []): array
    {
        $res = NavModel::search($param);
        $res = NavHandler::handleAll($res);
        $res = object_to_array($res);
        $res = Category::childrens(0 , $res , [
            'id'    => 'id' ,
            'p_id'  => 'p_id' ,
        ] , false , false);
        return self::success('' , $res);
    }

    public static function localUpdate(Base $context , $id , array $param = [])
    {
        $bool_range     = my_config_keys('business.bool_for_int');
        $validator = Validator::make($param , [
            'weight'    => 'sometimes|integer',
            'p_id'    => 'sometimes|integer',
            'module_id'    => 'sometimes|integer',
            'is_enabled'   => ['sometimes', Rule::in($bool_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $nav = NavModel::find($id);
        if (empty($nav)) {
            return self::error('分类不存在' , '' , 404);
        }
        $param['name']     = $param['name'] === '' ? $nav->name : $param['name'];
        $param['is_enabled']   = $param['is_enabled'] === '' ? $nav->is_enabled : $param['is_enabled'];
        $param['weight']    = $param['weight'] === '' ? $nav->weight : $param['weight'];
        $param['p_id']     = $param['p_id'] === '' ? $nav->p_id : $param['p_id'];
        $param['module_id']     = $param['module_id'] === '' ? $nav->module_id : $param['module_id'];
        $param['value']     = $param['value'] === '' ? $nav->value : $param['value'];

        NavModel::updateById($nav->id , array_unit($param , [
            'name' ,
            'is_enabled' ,
            'weight' ,
            'p_id' ,
            'module_id' ,
            'value' ,
        ]));
        return self::success('操作成功');
    }

    public static function update(Base $context , $id , array $param = [])
    {
        $bool_range = my_config_keys('business.bool_for_int');
        $validator = Validator::make($param , [
            'name'    => 'required',
            'is_enabled'   => ['required', Rule::in($bool_range)],
            'p_id'    => 'required|integer',
            'weight'    => 'sometimes|integer',
            'module_id'    => 'required|integer',
            'value'    => 'required',
            'type'    => 'required',
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $nav = NavModel::find($id);
        if (empty($nav)) {
            return self::error('分类不存在' , '' , 404);
        }
        $param['weight'] = $param['weight'] === '' ? 0 : $param['weight'];
        $param['updated_at'] = current_datetime();
        NavModel::updateById($nav->id , array_unit($param , [
            'name' ,
            'is_enabled' ,
            'weight' ,
            'p_id' ,
            'module_id' ,
            'value' ,
            'type' ,
            'updated_at' ,
        ]));
        return self::success('操作成功');
    }

    public static function store(Base $context , array $param = [])
    {
        $bool_range = my_config_keys('business.bool_for_int');
        $validator = Validator::make($param , [
            'name'    => 'required',
            'is_enabled'  => ['required', Rule::in($bool_range)],
            'p_id'    => 'required|integer',
            'weight'  => 'sometimes|integer',
            'module_id'  => 'required|integer',
            'value'    => 'required',
            'type'    => 'required',
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $param['weight'] = $param['weight'] === '' ? 0 : $param['weight'];
        $param['created_at'] = current_datetime();
        $id = NavModel::insertGetId(array_unit($param , [
            'name' ,
            'is_enabled' ,
            'weight' ,
            'p_id' ,
            'module_id' ,
            'value' ,
            'type' ,
            'created_at' ,
        ]));
        return self::success('' , $id);
    }

    public static function show(Base $context , $id , array $param = [])
    {
        $res = NavModel::find($id);
        if (empty($res)) {
            return self::error('分类不存在' , '' , 404);
        }
        $res = NavHandler::handle($res);

        // 附加：模块
        NavHandler::module($res);
        // 附加：上级
        NavHandler::parent($res , false);
        // 附加：分类
        NavHandler::category($res);

        return self::success('' , $res);
    }

    public static function destroy(Base $context , $id , array $param = [])
    {
        $data = NavModel::get();
        $data = object_to_array($data);
        $data = Category::childrens($id , $data , null , true , false);
        $ids = array_column($data , 'id');
        $count = NavModel::destroy($ids);
        return self::success('操作成功' , $count);
    }

    public static function destroyAll(Base $context , array $ids , array $param = [])
    {
        $destroy = [];
        $data = NavModel::get();
        $data = object_to_array($data);
        foreach ($ids as $v)
        {
            $_data = Category::childrens($v , $data , null , true , false);
            $_ids = array_column($_data , 'id');
            $destroy = array_merge($destroy , $_ids);
        }
        NavModel::destroy($destroy);
        return self::success('操作成功');
    }

    public static function allExcludeSelfAndChildren(Base $context , int $id , array $param = [])
    {
        $res = NavModel::getAll();
        $res = NavHandler::handleAll($res);
        $res = object_to_array($res);
        $exclude = Category::childrens($id , $res , null , true , false);
        $exclude_ids = array_column($exclude , 'id');
        for ($i = 0; $i < count($res); ++$i)
        {
            $cur = $res[$i];
            if (in_array($cur['id'] , $exclude_ids)) {
                unset($res[$i]);
                $i--;
            }
        }
        $res = Category::childrens(0 , $res , [
            'id'    => 'id' ,
            'p_id'  => 'p_id' ,
        ] , false , false);
        return self::success('' , $res);
    }

}
