<?php


namespace App\Http\Controllers\api\web;


use App\Customize\api\web\action\HistoryAction;
use function api\web\error;
use function api\web\success;

class History extends Base
{
    public function less()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['size'] = $param['size'] ?? '';
        $res = HistoryAction::lessHistory($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function index()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['relation_type'] = $param['relation_type'] ?? '';
        $param['value'] = $param['value'] ?? '';
        $param['size'] = $param['size'] ?? '';
        $res = HistoryAction::index($this , $param);
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

        $res = HistoryAction::destroyAll($this , $ids , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function store()
    {
        $param = $this->request->post();
        $param['module_id']      = $param['module_id'] ?? '';
        $param['relation_type']    = $param['relation_type'] ?? '';
        $param['relation_id']    = $param['relation_id'] ?? '';
        $res = HistoryAction::store($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }
}
