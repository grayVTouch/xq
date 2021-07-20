<?php


namespace App\Http\Controllers\api\web;


use App\Customize\api\web\action\VideoAction;
use function api\web\error;
use function api\web\success;

class Video extends Base
{
    public function incrementViewCount(int $id)
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $res = VideoAction::incrementViewCount($this , $id , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function incrementPlayCount(int $id)
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $res = VideoAction::incrementPlayCount($this , $id , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function praiseHandle(int $id)
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $res = VideoAction::praiseHandle($this , $id , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function record(int $id)
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['index'] = $param['index'] ?? '';
        $param['played_duration'] = $param['played_duration'] ?? '';
        $param['definition'] = $param['definition'] ?? '';
        $param['subtitle'] = $param['subtitle'] ?? '';
        $param['volume'] = $param['volume'] ?? '';
        $res = VideoAction::record($this , $id , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function newest()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['size']     = $param['size'] ?? '';
        $res = VideoAction::newest($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function hot()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['size']     = $param['size'] ?? '';
        $res = VideoAction::hot($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 最火图片标签
    public function hotTags()
    {
        $param = $this->request->query();

        $param['module_id'] = $param['module_id'] ?? '';
        $param['size']     = $param['size'] ?? '';
        $res = VideoAction::hotTags($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }

        return success($res['message'] , $res['data']);
    }

    public function hotTagsWithPager()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['size'] = $param['size'] ?? '';
        $param['value'] = $param['value'] ?? '';
        $res = VideoAction::hotTagsWithPager($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 标签对应内容
    public function getByTagId()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['tag_id']      = $param['tag_id'] ?? '';
        $param['size']     = $param['size'] ?? '';
        $res = VideoAction::getByTagId($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function index()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['category_ids'] = $param['category_ids'] ?? '';
        $param['video_series_ids'] = $param['video_series_ids'] ?? '';
        $param['video_company_ids'] = $param['video_company_ids'] ?? '';
        $param['tag_ids'] = $param['tag_ids'] ?? '';
        $param['order'] = $param['order'] ?? '';
        $param['value'] = $param['value'] ?? '';
        $param['mode'] = $param['mode'] ?? '';
        $res = VideoAction::index($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function category()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $res = VideoAction::category($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function show($id)
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $res = VideoAction::show($this , $id , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function recommend(int $id)
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['limit_id']     = $param['limit_id'] ?? '';
        $param['size']     = $param['size'] ?? '';
        $res = VideoAction::recommend($this , $id , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }
}
