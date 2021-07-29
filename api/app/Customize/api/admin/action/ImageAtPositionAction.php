<?php


namespace App\Customize\api\admin\action;

use App\Customize\api\admin\handler\ImageAtPositionHandler;
use App\Customize\api\admin\model\ImageAtPositionModel;
use App\Customize\api\admin\model\ModuleModel;
use App\Customize\api\admin\model\PositionModel;
use App\Customize\api\admin\repository\ResourceRepository;
use App\Http\Controllers\api\admin\Base;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function api\admin\get_form_error;
use function api\admin\my_config;
use function api\admin\parse_order;
use function core\array_unit;

class ImageAtPositionAction extends Action
{
    public static function index(Base $context , array $param = [])
    {
        $order = $param['order'] === '' ? [] : parse_order($param['order'] , '|');
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = ImageAtPositionModel::index([
            'module' ,
            'position' ,
        ] , $param , $order , $size);
        $res = ImageAtPositionHandler::handlePaginator($res);
        foreach ($res->data as $v)
        {
            // 附加：模块
            ImageAtPositionHandler::module($v);
            // 附加：位置
            ImageAtPositionHandler::position($v);
        }
        return self::success('' , $res);
    }

    public static function update(Base $context , $id , array $param = [])
    {
        $validator = Validator::make($param , [
            'module_id'     => 'required' ,
            'position_id'   => 'required' ,
            'src'          => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }

        $res = ImageAtPositionModel::find($id);
        if (empty($res)) {
            return self::error('id对应记录不存在' , '' , 404);
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $position = PositionModel::find($param['position_id']);
        if (empty($position)) {
            return self::error('位置不存在' , '' , 404);
        }
        $param['module_id'] = $module->id;
        $param['platform'] = $position->platform;
        try {
            DB::beginTransaction();
            ImageAtPositionModel::updateById($res->id , array_unit($param , [
                'position_id' ,
                'platform' ,
                'src' ,
                'link' ,
                'module_id' ,
            ]));
            ResourceRepository::used($param['src']);
            if ($res->src !== $param['src']) {
                ResourceRepository::delete($res->src);
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
        $validator = Validator::make($param , [
            'position_id'   => 'required' ,
            'module_id'     => 'required' ,
            'src'          => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $position = PositionModel::find($param['position_id']);
        if (empty($position)) {
            return self::error('位置不存在' , '' , 404);
        }
        $param['module_id'] = $module->id;
        $param['platform'] = $position->platform;
        try {
            DB::beginTransaction();
            $id = ImageAtPositionModel::insertGetId(array_unit($param , [
                'position_id' ,
                'platform' ,
                'src' ,
                'link' ,
                'module_id' ,
            ]));
            ResourceRepository::used($param['src']);
            DB::commit();
            return self::success('' , $id);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function show(Base $context , $id , array $param = [])
    {
        $res = ImageAtPositionModel::find($id);
        if (empty($res)) {
            return self::error('id 对应记录不存在' , '' , 404);
        }
        $res = ImageAtPositionHandler::handle($res);

        // 附加：模块
        ImageAtPositionHandler::module($res);
        // 附加：位置
        ImageAtPositionHandler::position($res);

        return self::success('' , $res);
    }

    public static function destroy(Base $context , $id , array $param = [])
    {
        $res = ImageAtPositionModel::find($id);
        if (empty($res)) {
            return self::error('待删除记录不存在' , '' , 404);
        }
        try {
            DB::beginTransaction();
            $count = ImageAtPositionModel::destroy($id);
            ResourceRepository::delete($res->src);
            DB::commit();
            return self::success($count);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function destroyAll(Base $context , array $ids , array $param = [])
    {
        try {
            DB::beginTransaction();
            $res = ImageAtPositionModel::find($ids);
            foreach ($res as $v)
            {
                ResourceRepository::delete($v->src);
            }
            $count = ImageAtPositionModel::destroy($ids);
            DB::commit();
            return self::success($count);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function search(Base $context , $value , array $param = [])
    {
        if (empty($value)) {
            return self::error('请提供搜索值');
        }
        $res = ImageAtPositionModel::search($value);
        $res = ImageAtPositionHandler::handleAll($res);
        return self::success('' , $res);
    }
}
