<?php


namespace App\Http\Controllers\api\admin;


use App\Customize\api\admin\action\VideoProjectAction;
use function api\admin\error;
use function api\admin\success;

class VideoProject extends Base
{
    public function index()
    {
        $param = $this->request->query();

        $param['id']               = $param['id'] ?? '';
        $param['name']             = $param['name'] ?? '';
        $param['module_id']        = $param['module_id'] ?? '';
        $param['category_id']  = $param['category_id'] ?? '';
        $param['video_series_id']  = $param['video_series_id'] ?? '';
        $param['video_company_id'] = $param['video_company_id'] ?? '';
        $param['order']            = $param['order'] ?? '';
        $param['size']            = $param['size'] ?? '';

        $res = VideoProjectAction::index($this , $param);

        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }

        return success($res['message'] , $res['data']);
    }

    public function update($id)
    {
        $param = $this->request->post();
        $param['name']          = $param['name'] ?? '';
        $param['thumb']         = $param['thumb'] ?? '';
        $param['score']          = $param['score'] ?? '';
        $param['release_date ']          = $param['release_date'] ?? '';
        $param['end_date']          = $param['end_date'] ?? '';
        $param['status']          = $param['status'] ?? '';
        $param['count']          = $param['count'] ?? '';
        $param['category_id']          = $param['category_id'] ?? '';
        $param['video_series_id']          = $param['video_series_id'] ?? '';
        $param['video_company_id']          = $param['video_company_id'] ?? '';
        $param['module_id']          = $param['module_id'] ?? '';
        $param['weight']          = $param['weight'] ?? '';
        $param['tags']          = $param['tags'] ?? '';
        $param['min_index']          = $param['min_index'] ?? '';
        $param['max_index']          = $param['max_index'] ?? '';

        $res = VideoProjectAction::update($this , $id ,$param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function store()
    {
        $param = $this->request->post();
        $param['name']          = $param['name'] ?? '';
        $param['thumb']         = $param['thumb'] ?? '';
        $param['score']          = $param['score'] ?? '';
        $param['release_date ']  = $param['release_date'] ?? '';
        $param['end_date']          = $param['end_date'] ?? '';
        $param['status']          = $param['status'] ?? '';
        $param['count']          = $param['count'] ?? '';
        $param['category_id']          = $param['category_id'] ?? '';
        $param['video_series_id']          = $param['video_series_id'] ?? '';
        $param['video_company_id']          = $param['video_company_id'] ?? '';
        $param['module_id']          = $param['module_id'] ?? '';
        $param['weight']          = $param['weight'] ?? '';
        $param['tags']          = $param['tags'] ?? '';
        $param['min_index']          = $param['min_index'] ?? '';
        $param['max_index']          = $param['max_index'] ?? '';

//        var_dump($param['release_date']);
        $res = VideoProjectAction::store($this ,$param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function show($id)
    {
        $res = VideoProjectAction::show($this , $id);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function destroy($id)
    {
        $res = VideoProjectAction::destroy($this , $id);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function destroyAll()
    {
        $ids = $this->request->input('ids' , '[]');
        $ids = json_decode($ids , true);
        $res = VideoProjectAction::destroyAll($this , $ids);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function destroyTag()
    {
        $param = $this->request->post();
        $param['video_project_id'] = $param['video_project_id'] ?? '';
        $param['tag_id'] = $param['tag_id'] ?? '';
        $res = VideoProjectAction::destroyTag($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function search()
    {
        $param = $this->request->query();
        $param['value'] = $param['value'] ?? '';
        $param['size'] = $param['size'] ?? '';
        $param['module_id'] = $param['module_id'] ?? '';
        $res = VideoProjectAction::search($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }
}
