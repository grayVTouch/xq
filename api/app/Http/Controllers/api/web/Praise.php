<?php


namespace App\Http\Controllers\api\web;


use App\Customize\api\web\action\PraiseAction;
use function api\web\error;
use function api\web\success;

class Praise extends Base
{
    public function index()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['relation_type'] = $param['relation_type'] ?? '';
        $param['value'] = $param['value'] ?? '';
        $param['size'] = $param['size'] ?? '';
        $res = PraiseAction::index($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function destroyAll()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $ids = $param['ids'] ?? '[]';
        $ids = json_decode($ids , true);
        $res = PraiseAction::destroyAll($this , $ids , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 点赞 & 取消点赞
    public function createOrCancel()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['relation_type']    = $param['relation_type'] ?? '';
        $param['relation_id']    = $param['relation_id'] ?? '';
        $param['action']    = $param['action'] ?? '';
        $res = PraiseAction::createOrCancel($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }
}
