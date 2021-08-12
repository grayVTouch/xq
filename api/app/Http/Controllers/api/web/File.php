<?php


namespace App\Http\Controllers\api\web;


use App\Customize\api\web\action\FileAction;
use App\Customize\api\web\facade\AliyunOss;
use function api\web\error;
use function api\web\success;

class File extends Base
{

    public function upload()
    {
        $param = $this->request->post();
        $param['file'] = $this->request->file('file');
        $res = FileAction::upload($this , $param['file'] , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function uploadImage()
    {
        $param = $this->request->query();
        $param['m'] = $param['m'] ?? '';
        $param['w'] = $param['w'] ?? '';
        $param['h'] = $param['h'] ?? '';
        $param['r'] = $param['r'] ?? '';
        // 仅在 disk = cloud 的时候有效
        $param['is_upload_to_cloud'] = $param['is_upload_to_cloud'] ?? '';
        $param['file'] = $this->request->file('file');
        $res = FileAction::uploadImage($this , $param['file'] , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function uploadVideo()
    {
        $param = $this->request->post();
        $param['name'] = $param['name'] ?? '';
        $param['size'] = $param['size'] ?? '';
        $param['total'] = $param['total'] ?? '';
        $param['index'] = $param['index'] ?? '';
        $param['md5'] = $param['md5'] ?? '';
        $param['file'] = $this->request->file('file' , null);
        $res = FileAction::uploadVideo($this , $param['file'] , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function uploadSubtitle()
    {
        $param = $this->request->post();
        $param['file'] = $this->request->file('file');
        $res = FileAction::uploadSubtitle($this , $param['file'] , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function uploadOffice()
    {
        $param = $this->request->post();
        $param['file'] = $this->request->file('file');
        $res = FileAction::uploadOffice($this , $param['file'] , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function test()
    {
        $param = $this->request->post();
        $u_file = $this->request->file('file');

        $bucket = 'running-xinqu';
        $file = "D:\image\\0016.jpg";

        $url = 'http://running-xinqu.oss-cn-hangzhou.aliyuncs.com/20210724/20210724015524Hb5tG7.jpg';

//        $res = AliyunOss::upload($bucket , 'hello.png' , $file);
        $res = AliyunOss::delete($bucket , $url);
        if ($res['code'] > 0) {
            return error($res['message'] , $res['data']);
        }
        return success($res['message'] , $res['data']);
    }
}
