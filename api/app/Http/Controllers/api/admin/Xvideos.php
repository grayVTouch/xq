<?php


namespace App\Http\Controllers\api\admin;


use App\Customize\api\admin\action\XvideosAction;
use Illuminate\Http\JsonResponse;
use function api\admin\error;
use function api\admin\success;

class Xvideos extends Base
{

    public function parse(): JsonResponse
    {
        $param = $this->request->post();
        $param['src']       = $param['src'] ?? '';
        $res = XvideosAction::parse($this , $param);
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
        $res = XvideosAction::download($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }
}
