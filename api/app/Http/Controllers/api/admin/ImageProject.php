<?php


namespace App\Http\Controllers\api\admin;


use App\Customize\api\admin\action\ImageProjectAction;
use function api\admin\error;
use function api\admin\success;

class ImageProject extends Base
{
    public function index()
    {
        $param = $this->request->query();

        $param['id']           = $param['id'] ?? '';
        $param['name']         = $param['name'] ?? '';
        $param['user_id']      = $param['user_id'] ?? '';
        $param['module_id']    = $param['module_id'] ?? '';
        $param['category_id']  = $param['category_id'] ?? '';
        $param['image_subject_id']   = $param['image_subject_id'] ?? '';
        $param['type']         = $param['type'] ?? '';
        $param['status']       = $param['status'] ?? '';
        $param['order']        = $param['order'] ?? '';
        $param['size']        = $param['size'] ?? '';

        $res = ImageProjectAction::index($this , $param);

        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }

        return success($res['message'] , $res['data']);
    }

    public function update($id)
    {
        $param = $this->request->post();
        $param['name']          = $param['name'] ?? '';
        $param['user_id']       = $param['user_id'] ?? '';
        $param['module_id']     = $param['module_id'] ?? '';
        $param['category_id']   = $param['category_id'] ?? '';
        $param['type']          = $param['type'] ?? '';
        $param['image_subject_id']    = $param['image_subject_id'] ?? '';
        $param['tags']           = $param['tags'] ?? '';
        $param['thumb']         = $param['thumb'] ?? '';
        $param['description']   = $param['description'] ?? '';
        $param['weight']        = $param['weight'] ?? '';
        $param['view_count']    = $param['view_count'] ?? '';
        $param['praise_count']  = $param['praise_count'] ?? '';
        $param['status']        = $param['status'] ?? '';
        $param['fail_reason']   = $param['fail_reason'] ?? '';
        $param['images']   = $param['images'] ?? '';
        $param['created_at']   = $param['created_at'] ?? '';
        $res = ImageProjectAction::update($this , $id ,$param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function store()
    {
        $param = $this->request->post();
        $param['name']          = $param['name'] ?? '';
        $param['user_id']       = $param['user_id'] ?? '';
        $param['module_id']     = $param['module_id'] ?? '';
        $param['category_id']   = $param['category_id'] ?? '';
        $param['type']          = $param['type'] ?? '';
        $param['image_subject_id']    = $param['image_subject_id'] ?? '';
        $param['tags']           = $param['tags'] ?? '';
        $param['thumb']         = $param['thumb'] ?? '';
        $param['description']   = $param['description'] ?? '';
        $param['weight']        = $param['weight'] ?? '';
        $param['view_count']    = $param['view_count'] ?? '';
        $param['praise_count']  = $param['praise_count'] ?? '';
        $param['status']        = $param['status'] ?? '';
        $param['fail_reason']   = $param['fail_reason'] ?? '';
        $param['images']   = $param['images'] ?? '';
        $param['created_at']   = $param['created_at'] ?? '';
        $res = ImageProjectAction::store($this ,$param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function show($id)
    {
        $res = ImageProjectAction::show($this , $id);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function destroy($id)
    {
        $res = ImageProjectAction::destroy($this , $id);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function destroyAll()
    {
        $ids = $this->request->input('ids' , '[]');
        $ids = json_decode($ids , true);
        $res = ImageProjectAction::destroyAll($this , $ids);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function destroyImages()
    {
        $ids = $this->request->input('ids' , '[]');
        $ids = json_decode($ids , true);
        $res = ImageProjectAction::destroyImages($this , $ids);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function destroyTag()
    {
        $param = $this->request->post();
        $param['image_project_id'] = $param['image_project_id'] ?? '';
        $param['tag_id'] = $param['tag_id'] ?? '';
        $res = ImageProjectAction::destroyTag($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 重新运行队列
    public function retry()
    {
        $ids = $this->request->post('ids' , []);
        $ids = json_decode($ids , true);
        $res = ImageProjectAction::retry($this , $ids);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function updateProcessStatus()
    {
        $param = $this->request->post();
        $param['status'] = $param['status'] ?? '';
        $param['ids'] = $param['ids'] ?? '';
        $res = ImageProjectAction::updateProcessStatus($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }
}
