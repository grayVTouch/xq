<?php


namespace App\Http\Controllers\api\web;


use App\Customize\api\web\action\CollectionGroupAction;
use function api\web\error;
use function api\web\success;

class CollectionGroup extends Base
{


    // 删除收藏夹
    public function destroy(int $id)
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $res = CollectionGroupAction::destroy($this , $id , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 删除收藏夹
    public function destroyAll()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $ids = $param['ids'] ?? '[]';
        $ids = json_decode($param['ids'] , true);
        $res = CollectionGroupAction::destroyAll($this , $ids , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 收藏夹列表-带有判断（判断某个事物是否存在于此）
    public function getWithJudge()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['relation_type'] = $param['relation_type'] ?? '';
        $param['relation_id'] = $param['relation_id'] ?? '';
        $res = CollectionGroupAction::getWithJudge($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 收藏夹列表-带搜索
    public function index()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['relation_type'] = $param['relation_type'] ?? '';
        $param['value'] = $param['value'] ?? '';
        $res = CollectionGroupAction::index($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }


    // 收藏 & 取消收藏
    public function collectOrCancel(int $id)
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['relation_type']    = $param['relation_type'] ?? '';
        $param['relation_id']    = $param['relation_id'] ?? '';
        $param['action']    = $param['action'] ?? '';
        $res = CollectionGroupAction::collectOrCancel($this , $id , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 创建并加入收藏夹
    public function createAndJoin()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['name'] = $param['name'] ?? '';
        $param['relation_type']    = $param['relation_type'] ?? '';
        $param['relation_id']    = $param['relation_id'] ?? '';
        $res = CollectionGroupAction::createAndJoin($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 仅创建收藏夹
    public function store()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['name'] = $param['name'] ?? '';
        $res = CollectionGroupAction::store($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 仅加入收藏夹
    public function join()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['collection_group_id'] = $param['collection_group_id'] ?? '';
        $param['relation_type']    = $param['relation_type'] ?? '';
        $param['relation_id']    = $param['relation_id'] ?? '';
        $res = CollectionGroupAction::join($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }


    // 收藏夹 - 收藏内容 列表
    public function lessCollection(int $id)
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['size'] = $param['size'] ?? '';
        $res = CollectionGroupAction::lessCollection($this , $id , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function getWithCollection()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['collection_group_limit'] = $param['collection_group_limit'] ?? '';
        $param['collection_limit'] = $param['collection_limit'] ?? '';
        $res = CollectionGroupAction::getWithCollection($this , $param);

        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function collections(int $id)
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['relation_type'] = $param['relation_type'] ?? '';
        $param['value'] = $param['value'] ?? '';
        $param['size'] = $param['size'] ?? '';
        $res = CollectionGroupAction::collections($this , $id , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function update(int $id)
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['name'] = $param['name'] ?? '';
        $res = CollectionGroupAction::update($this , $id , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }


    public function show(int $id)
    {
        $res = CollectionGroupAction::show($this , $id);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }



}
