<?php


namespace App\Http\Controllers\api\admin;


use App\Customize\api\admin\action\SystemSettingsAction;
use Illuminate\Http\JsonResponse;
use function api\admin\error;
use function api\admin\success;

class SystemSettings extends Base
{

    public function data(): JsonResponse
    {
        $res = SystemSettingsAction::data($this);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function update(): JsonResponse
    {
        $param = $this->request->post();
        $param['web_url'] = $param['web_url'] ?? '';
        $param['is_enable_grapha_verify_code_for_login'] = $param['is_enable_grapha_verify_code_for_login'] ?? '';
        $param['web_route_mappings'] = $param['web_route_mappings'] ?? '';
        $param['disk'] = $param['disk'] ?? '';
        $param['aliyun_key'] = $param['aliyun_key'] ?? '';
        $param['aliyun_secret'] = $param['aliyun_secret'] ?? '';
        $param['aliyun_endpoint'] = $param['aliyun_endpoint'] ?? '';
        $param['aliyun_bucket'] = $param['aliyun_bucket'] ?? '';
        $param['friend_links'] = $param['friend_links'] ?? '';
        $res = SystemSettingsAction::update($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function loginSettings(): JsonResponse
    {
        $res = SystemSettingsAction::loginSettings($this);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }


}
