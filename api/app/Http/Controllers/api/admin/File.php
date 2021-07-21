<?php


namespace App\Http\Controllers\api\admin;


use App\Customize\api\admin\action\FileAction;
use App\Customize\api\admin\facade\AliyunOss;
use Illuminate\Http\Request;
use OSS\Core\OssException;
use OSS\OssClient;
use function api\admin\error;
use function api\admin\success;

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
        $param['file'] = $this->request->file('file');
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

        // 阿里云主账号AccessKey拥有所有API的访问权限，风险很高。强烈建议您创建并使用RAM账号进行API访问或日常运维，请登录https://ram.console.aliyun.com创建RAM账号。
        $accessKeyId = "LTAI7F0gAA1b6YXI";
        $accessKeySecret = "XqWmTvjPm0adiaE1e1s3M61bHn22Yd";
        // Endpoint以杭州为例，其它Region请按实际情况填写。
        $endpoint = "http://oss-cn-hangzhou.aliyuncs.com";

//        AliyunOss::upload();
        $bucket = '';
        AliyunOss::upload();
    }
}
