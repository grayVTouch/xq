<?php


namespace App\Http\Controllers\api\admin;


use App\Customize\api\admin\action\PornhubAction;
use Illuminate\Http\JsonResponse;
use function api\admin\error;
use function api\admin\success;

class Pornhub extends Base
{

    public function parse(): JsonResponse
    {
        $param = $this->request->post();
        $param['src']       = $param['src'] ?? '';
        $res = PornhubAction::parse($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function download(): JsonResponse
    {
        $param = $this->request->post();
        $param['save_dir'] = $param['save_dir'] ?? '';
        $param['filename'] = $param['filename'] ?? '';
        $param['definition'] = $param['definition'] ?? '';
        $param['src'] = $param['src'] ?? '';
        $param['proxy_pass'] = $param['proxy_pass'] ?? '';
        $res = PornhubAction::download($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

}
