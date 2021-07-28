<?php


namespace App\Http\Controllers\api\web;


use App\Customize\api\web\action\CollectionAction;
use App\Customize\api\web\action\CollectionGroupAction;
use function api\web\error;
use function api\web\success;

class Collection extends Base
{
    public function destroy(int $id)
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $res = CollectionAction::destroy($this , $id , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 收藏夹列表-带搜索
    public function index()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['user_id'] = $param['user_id'] ?? '';
        $param['relation_type'] = $param['relation_type'] ?? '';
        $param['value'] = $param['value'] ?? '';
        $res = CollectionAction::index($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }
}
