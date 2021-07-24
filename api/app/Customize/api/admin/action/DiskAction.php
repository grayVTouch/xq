<?php


namespace App\Customize\api\admin\action;

use App\Customize\api\admin\handler\DiskHandler;
use App\Customize\api\admin\model\ModuleModel;
use App\Customize\api\admin\model\DiskModel;
use App\Http\Controllers\api\admin\Base;
use Core\Lib\File;
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
use function core\format_path;

class DiskAction extends Action
{
    public static function index(Base $context , array $param = [])
    {
        $order = $param['order'] === '' ? [] : parse_order($param['order'] , '|');
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = DiskModel::index($param , $order , $size);
        $res = DiskHandler::handlePaginator($res);
        return self::success('' , $res);
    }

    public static function localUpdate(Base $context , $id , array $param = [])
    {
        $bool_range = my_config_keys('business.bool_for_int');
        $os_range   = my_config_keys('business.os_for_disk');

        $validator = Validator::make($param , [
            'is_default'   => ['sometimes' , 'integer' , Rule::in($bool_range)] ,
            'os'        => ['sometimes' , Rule::in($os_range)] ,
            'is_linked' => ['sometimes' , 'integer' , Rule::in($bool_range)] ,
        ]);

        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }

        $res = DiskModel::find($id);

        if (empty($res)) {
            return self::error('标签不存在' , '' , 404);
        }

        if (empty($param['prefix'])) {
            $repeat = DiskModel::findByExcludeIdAndPrefix($res->id , $param['prefix']);
            if (!empty($repeat)) {
                return self::error('前缀已经存在，请重新输入');
            }
        }

        if (!empty($param['path'])) {
            if (!File::isDir($param['path'])) {
                return self::error('表单错误' , [
                    'path' => '请提供真实有效的磁盘目录绝对路径' ,
                ]);
            }
        }

        $param['path']      = $param['path'] === '' ? $res->path : $param['path'];
        $param['os']        = $param['os'] === '' ? $res->os : $param['os'];
        $param['prefix']    = $param['prefix'] === '' ? $res->prefix : $param['prefix'];
        $param['is_default']   = $param['is_default'] === '' ? $res->is_default : $param['is_default'];
        $param['is_linked'] = $param['is_linked'] === '' ? $res->is_linked : $param['is_linked'];

        try {
            DB::beginTransaction();
            DiskModel::updateById($res->id , array_unit($param , [
                'path' ,
                'os' ,
                'prefix' ,
                'is_default' ,
                'is_linked' ,
                'updated_at' ,
            ]));
            if ($param['is_default']) {
                DiskModel::setNotDefaultByExcludeId($res->id);
            }
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function update(Base $context , $id , array $param = [])
    {
        $bool_range = my_config_keys('business.bool_for_int');
        $os_range   = my_config_keys('business.os_for_disk');

        $validator = Validator::make($param , [
            'path'      => 'required' ,
            'prefix'    => 'required' ,
            'is_default'   => ['required' , 'integer' , Rule::in($bool_range)] ,
            'is_linked' => ['required' , 'integer' , Rule::in($bool_range)] ,
            'os'        => ['required' , Rule::in($os_range)] ,
        ]);

        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $res = DiskModel::find($id);
        if (empty($res)) {
            return self::error('标签不存在' , '' , 404);
        }
        if (!File::isDir($param['path'])) {
            return self::error('请提供真实有效的磁盘目录绝对路径');
        }
        if (!empty(DiskModel::findByExcludeIdAndPrefix($res->id , $param['prefix']))) {
            return self::error('前缀已经存在，请重新提供');
        }
        if (!empty(DiskModel::findByExcludeIdAndPath($res->id , $param['path']))) {
            return self::error('路径已经存在，请重新提供');
        }
        $param['path'] = format_path($param['path']);
        try {
            DB::beginTransaction();
            DiskModel::updateById($res->id , array_unit($param , [
                'path' ,
                'os' ,
                'prefix' ,
                'is_default' ,
                'is_linked' ,
                'updated_at' ,
            ]));
            if ($param['is_default']) {
                DiskModel::setNotDefaultByExcludeId($res->id);
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
        $bool_range = my_config_keys('business.bool_for_int');
        $os_range   = my_config_keys('business.os_for_disk');
        $validator = Validator::make($param , [
            'path'      => 'required' ,
            'prefix'    => 'required' ,
            'is_default'   => ['required' , 'integer' , Rule::in($bool_range)] ,
            'is_linked'   => ['sometimes' , 'integer' , Rule::in($bool_range)] ,
            'os'        => ['required' , Rule::in($os_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        if (!empty(DiskModel::findByPrefix($param['prefix']))) {
            return self::error('前缀已经存在，请重新提供');
        }
        if (!File::isDir($param['path'])) {
            return self::error('请提供真实有效的磁盘目录绝对路径');
        }
        if (!empty(DiskModel::findByPath($param['path']))) {
            return self::error('路径已经存在，请重新提供');
        }
        $param['path'] = format_path($param['path']);
        $param['created_at'] = current_datetime();
        try {
            DB::beginTransaction();
            $id = DiskModel::insertGetId(array_unit($param , [
                'path' ,
                'os' ,
                'prefix' ,
                'is_linked' ,
                'is_default' ,
                'created_at' ,
            ]));
            if ($param['is_default'] == 1) {
                DiskModel::setNotDefaultByExcludeId($id);
            }
            DB::commit();
            if ($param['is_linked'] == 1) {
                $res_dir    = my_config('app.res_dir');
                $res_dir    = rtrim($res_dir , '/');
                // 创建软连接
                $link   = $res_dir . '/' . $param['prefix'];
                $status = 0;
                $res    = [];
                if (in_array($param['os'] , ['windows'])) {
                    exec("mklink /J \"{$link}\" \"{$param['path']}\"" , $res , $status);
                } else {
                    // linux | mac os
                    exec("ln -s \"{$param['path']}\" \"{$link}\"" , $res , $status);
                }
                if ($status > 0) {
                    return self::success('操作成功【仅软连接创建失败】' , $res);
                }
            }
            return self::success('操作成功');
        } catch(Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }

    public static function show(Base $context , $id , array $param = [])
    {
        $res = DiskModel::find($id);
        if (empty($res)) {
            return self::error('记录不存在' , '' , 404);
        }
        $res = DiskHandler::handle($res);
        return self::success('' , $res);
    }

    public static function destroy(Base $context , $id , array $param = [])
    {
        $count = DiskModel::destroy($id);
        return self::success('操作成功' , $count);
    }

    public static function destroyAll(Base $context , array $ids , array $param = [])
    {
        $count = DiskModel::destroy($ids);
        return self::success('操作成功' , $count);
    }

    public static function link(Base $context , array $ids , array $param = []): array
    {
        $disks = [];
        foreach ($ids as $v)
        {
            $disk = DiskModel::find($v);
            if (empty($disk)) {
                return self::error("部分id【{$v}】对应的记录不存在" , $v , 404);
            }
            if ($disk->is_linked) {
                return self::error('包含已创建链接的记录');
            }
            $disks[] = $disk;
        }
        $res_dir    = my_config('app.res_dir');
        $res_dir    = rtrim($res_dir , '/');
        $failed     = [];
        foreach ($disks as $v)
        {
            // 创建软连接
            $link   = $res_dir . '/' . $v->prefix;
            $status = 0;
            $res    = [];
            if (in_array($v->os , ['windows'])) {
                exec("mklink /J \"{$link}\" \"{$v->path}\"" , $res , $status);
            } else {
                // linux | mac os
                exec("ln -s \"{$v->path}\" \"{$link}\"" , $res , $status);
            }
            if ($status > 0) {
                $failed[] = implode('' , $res);
            }
            DiskModel::updateById($v->id , [
                'is_linked' => 1 ,
            ]);
        }
        if (count($failed) > 0) {
            return self::success("部分创建成功" . (empty($failed[0]) ? '' : "【{$failed[0]}】") , $failed);
        }
        return self::success('操作成功');
    }

}
