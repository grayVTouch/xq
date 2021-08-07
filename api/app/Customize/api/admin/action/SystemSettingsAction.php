<?php


namespace App\Customize\api\admin\action;

use App\Customize\api\admin\model\SystemSettingsModel;
use App\Customize\api\admin\model\WebRouteMappingModel;
use App\Http\Controllers\api\admin\Base;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function api\admin\my_config_keys;

class SystemSettingsAction extends Action
{
    public static function data(Base $context , array $param = []): array
    {
        $system_settings = SystemSettingsModel::first();
        $web_route_mappings = WebRouteMappingModel::all();
        $data = [
            'system_settings' => $system_settings ,
            'web_route_mappings' => $web_route_mappings ,
        ];
        return self::success('' , $data);
    }

    public static function update(Base $context , array $param = []): array
    {
        $bool_range = my_config_keys('business.bool_for_int');
        $disk_range = my_config_keys('business.disk');
        $validator = Validator::make($param , [
            'is_enable_grapha_verify_code_for_login' => ['required' , Rule::in($bool_range)] ,
            'disk' => ['required' , Rule::in($disk_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $system_settings = SystemSettingsModel::first();
        if (empty($system_settings)) {
            return self::error('系统设置不存在' , '' , 404);
        }
        $param['web_url'] = rtrim($param['web_url'] , '/');
        $web_route_mappings = empty($param['web_route_mappings']) ? [] : json_decode($param['web_route_mappings'] , true);
        $web_route_mappings = array_map(function($v){
            $v['url'] = ltrim($v['url'] , '/');
            $v['url'] = '/' . $v['url'];
            return $v;
        } , $web_route_mappings);
        try {
            DB::beginTransaction();
            SystemSettingsModel::updateById($system_settings->id , [
                'web_url' => $param['web_url'] ,
                'is_enable_grapha_verify_code_for_login' => $param['is_enable_grapha_verify_code_for_login'] ,
                'disk' => $param['disk'] ,
                'aliyun_key' => $param['aliyun_key'] ,
                'aliyun_secret' => $param['aliyun_secret'] ,
                'aliyun_endpoint' => $param['aliyun_endpoint'] ,
                'aliyun_bucket' => $param['aliyun_bucket'] ,
                'proxy_pass' => $param['proxy_pass'] ,
            ]);
            foreach ($web_route_mappings as $v)
            {
                WebRouteMappingModel::updateById($v['id'] , [
                    'url' => $v['url']
                ]);
            }
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function loginSettings(Base $context , array $param = []): array
    {
        $system_settings = SystemSettingsModel::first();
        $data = [
            'is_enable_grapha_verify_code_for_login' => $system_settings->is_enable_grapha_verify_code_for_login ,
        ];
        return self::success('' , $data);
    }

}
