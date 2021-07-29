<?php


namespace App\Customize\api\admin\action;



use App\Customize\api\admin\handler\CategoryHandler;
use App\Customize\api\admin\model\CategoryModel;
use App\Customize\api\admin\model\UserModel;
use App\Http\Controllers\api\admin\Base;
use Core\Lib\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function api\admin\get_form_error;
use function api\admin\my_config_keys;
use function core\array_unit;
use function core\object_to_array;

class CategoryAction extends Action
{
    public static function index(Base $context , array $param = [])
    {
        $param['type'] = empty($param['type']) ? [] : array_filter(explode(',' , $param['type']));
        $res = CategoryModel::getByRelationAndFilter([
            'module' ,
            'user' ,
            'parent' ,
        ] , $param);
        $res = CategoryHandler::handleAll($res);
        foreach ($res as $v)
        {
            // 附加：模块
            CategoryHandler::module($v);
            // 附加：用户
            CategoryHandler::user($v);
            // 附加：主体
            CategoryHandler::parent($v , false);
        }
        $res = object_to_array($res);
        $res = Category::childrens(0 , $res , [
            'id'    => 'id' ,
            'p_id'  => 'p_id' ,
        ] , false , false);
        return self::success('' , $res);
    }

    public static function search(Base $context , array $param = [])
    {
        $param['type'] = empty($param['type']) ? [] : array_filter(explode(',' , $param['type']));
        $res = CategoryModel::getByRelationAndFilter([] , $param);
        $res = CategoryHandler::handleAll($res);
        $res = object_to_array($res);
        $res = Category::childrens(0 , $res , [
            'id'    => 'id' ,
            'p_id'  => 'p_id' ,
        ] , false , false);
        return self::success('' , $res);
    }

    public static function localUpdate(Base $context , $id , array $param = [])
    {
        $bool_range = my_config_keys('business.bool_for_int');
        $validator = Validator::make($param , [
            'is_enabled'   => ['sometimes', Rule::in($bool_range)],
            'weight'    => 'sometimes|integer',
            'p_id'    => 'sometimes|integer',
            'module_id'    => 'sometimes|integer',
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $category = CategoryModel::find($id);
        if (empty($category)) {
            return self::error('分类不存在' , '' , 404);
        }
        $param['name']     = $param['name'] === '' ? $category->name : $param['name'];
        $param['description'] = $param['description'] === '' ? $category->description : $param['description'];
        $param['is_enabled']   = $param['is_enabled'] === '' ? $category->is_enabled : $param['is_enabled'];
        $param['weight']    = $param['weight'] === '' ? $category->weight : $param['weight'];
        $param['p_id']     = $param['p_id'] === '' ? $category->p_id : $param['p_id'];
        $param['module_id']     = $param['module_id'] === '' ? $category->module_id : $param['module_id'];

        CategoryModel::updateById($category->id , array_unit($param , [
            'name' ,
            'description' ,
            'is_enabled' ,
            'weight' ,
            'p_id' ,
            'module_id' ,
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
            'user_id'       => 'required|integer',
            'type'          => 'required',
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $category = CategoryModel::find($id);
        if (empty($category)) {
            return self::error('分类不存在' , '' , 404);
        }
        $user = UserModel::find($param['user_id']);
        if (empty($user)) {
            return self::error('用户不存在');
        }
        if ($param['status'] !== '' && $param['status'] == -1 && $param['fail_reason'] === '') {
            return self::error('请提供失败原因');
        }
        $param['weight'] = $param['weight'] === '' ? 0 : $param['weight'];
        CategoryModel::updateById($category->id , array_unit($param , [
            'name' ,
            'description' ,
            'is_enabled' ,
            'weight' ,
            'p_id' ,
            'module_id' ,
            'user_id' ,
            'type' ,
            'status' ,
            'fail_reason' ,
        ]));
        return self::success('操作成功');
    }

    public static function store(Base $context , array $param = [])
    {
        $bool_range = my_config_keys('business.bool_for_int');
        $validator = Validator::make($param , [
            'name'          => 'required',
            'is_enabled'    => ['required', Rule::in($bool_range)],
            'p_id'          => 'required|integer',
            'weight'        => 'sometimes|integer',
            'module_id'     => 'required|integer',
            'user_id'       => 'required|integer',
            'type'          => 'required',
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $user = UserModel::find($param['user_id']);
        if (empty($user)) {
            return self::error('用户不存在');
        }

        if ($param['status'] !== '' && $param['status'] == -1 && $param['fail_reason'] === '') {
            return self::error('请提供失败原因');
        }
        $param['weight'] = $param['weight'] === '' ? 0 : $param['weight'];
        $id = CategoryModel::insertGetId(array_unit($param , [
            'name' ,
            'description' ,
            'is_enabled' ,
            'weight' ,
            'p_id' ,
            'p_id' ,
            'module_id' ,
            'user_id' ,
            'type' ,
            'status' ,
            'fail_reason' ,
        ]));
        return self::success('' , $id);
    }

    public static function show(Base $context , $id , array $param = [])
    {
        $category = CategoryModel::find($id);
        if (empty($category)) {
            return self::error('分类不存在' , '' , 404);
        }
        $category = CategoryHandler::handle($category);

        // 附加：模块
        CategoryHandler::module($category);
        // 附加：用户
        CategoryHandler::user($category);
        // 附加：主体
        CategoryHandler::parent($category , false);

        return self::success('' , $category);
    }

    public static function destroy(Base $context , $id , array $param = [])
    {
        $data = CategoryModel::get();
        $data = object_to_array($data);
        $data = Category::childrens($id , $data , null , true , false);
        $ids = array_column($data , 'id');
        $count = CategoryModel::destroy($ids);
        return self::success('操作成功' , $count);
    }

    public static function destroyAll(Base $context , array $ids , array $param = [])
    {
        $destroy = [];
        $data = CategoryModel::get();
        $data = object_to_array($data);
        foreach ($ids as $v)
        {
            $_data = Category::childrens($v , $data , null , true , false);
            $_ids = array_column($_data , 'id');
            $destroy = array_merge($destroy , $_ids);
        }
        CategoryModel::destroy($destroy);
        return self::success('操作成功');
    }

    public static function allExcludeSelfAndChildren(Base $context , int $id , array $param = [])
    {
        $res = CategoryModel::getAll();
        $res = CategoryHandler::handleAll($res);
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
