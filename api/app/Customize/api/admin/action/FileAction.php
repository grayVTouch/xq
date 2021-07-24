<?php


namespace App\Customize\api\admin\action;


use App\Customize\api\admin\facade\AliyunOss;
use App\Customize\api\admin\model\SystemSettingsModel;
use App\Customize\api\admin\repository\FileRepository;
use App\Customize\api\admin\repository\ResourceRepository;
use App\Customize\api\admin\util\Util;
use App\Http\Controllers\api\admin\Base;
use Core\Lib\ImageProcessor;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function api\admin\my_config;
use function api\admin\get_form_error;
use function api\admin\my_config_keys;
use function core\random;

class FileAction extends Action
{
    // 上传文件（任意类型文件）
    public static function upload(Base $context , ?UploadedFile $file , array $param = [])
    {
        $validator = Validator::make($param , [
            'file' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $settings = SystemSettingsModel::first();
        try {
            Util::systemPowerUp();
            if ($settings->disk === 'local') {
                $dir        = date('Ymd');
                $path       = FileRepository::upload($file , $dir);
                $real_path  = FileRepository::generateRealPathByWithPrefixRelativePath($path);
                $url        = FileRepository::generateUrlByRelativePath($path);
                ResourceRepository::create($url , $real_path , 'local' , 0 , 0);
            } else if ($settings->disk === 'aliyun') {
                $filename = AliyunOss::generateFilename($file->getClientOriginalExtension());
                $res = AliyunOss::upload($settings->aliyun_bucket , $filename , $file->getPathname());
                if ($res['code'] > 0) {
                    return self::error($res['message'] , $res['data'] , 500);
                }
                $url = $res['data'];
                ResourceRepository::createAliyun($url , $settings->aliyun_bucket , 0 , 0);
            } else {
                // todo 预留
            }
            Util::systemPowerDown();
            return self::success('' , $url);
        } catch(Exception $e) {
            Util::systemPowerDown();
            return self::error($e->getMessage() , $e->getTraceAsString());
        }
    }

    /**
     * @param Base $context
     * @param UploadedFile|null $file
     * @param array $param
     * @return array
     * @throws \Exception
     */
    public static function uploadImage(Base $context , ?UploadedFile $file , array $param = []): array
    {
        $bool_range = my_config_keys('business.bool_for_int');
        $mode_range = my_config('business.mode_for_file');
        $validator = Validator::make($param , [
            'm' => ['sometimes' , Rule::in($mode_range)] ,
            'w' => 'sometimes|integer' ,
            'h' => 'sometimes|integer' ,
            'is_upload_to_cloud' => ['sometimes' , 'integer' , Rule::in($bool_range)] ,
//            'file' => 'required|mimes:jpg,jpeg,png,gif,webp' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $is_upload_to_cloud = $param['is_upload_to_cloud'] === '' ? 1 : (int) $param['is_upload_to_cloud'];
        $is_upload_to_cloud = (bool) $is_upload_to_cloud;
        $mime_range = ['jpg','jpeg','png','gif','webp','bmp'];
        $extension = $file->getClientOriginalExtension();
        $extension = strtolower($extension);
        if (!in_array($extension , $mime_range)) {
            return self::error("不支持的格式【{$extension}】，当前支持的格式有：" . implode(',' , $mime_range));
        }
        $settings = SystemSettingsModel::first();
        try {
            Util::systemPowerUp();
            if (empty($param['m'])) {
                if (!empty($param['w'])) {
                    $mode = 'fix-width';
                    if (!empty($param['h'])) {
                        $mode = 'fix';
                    }
                } else if (!empty($param['h'])) {
                    $mode = 'fix-height';
                } else {
                    $mode = '';
                }
            } else {
                $mode = $param['m'];
            }
            $url = '';
            $real_path = '';
            if (!$is_upload_to_cloud || $settings->disk === 'local' || in_array($mode , $mode_range)) {
                $dir        = date('Ymd');
                $path       = FileRepository::upload($file , $dir);
                $real_path  = FileRepository::generateRealPathByWithPrefixRelativePath($path);
                $url        = FileRepository::generateUrlByRelativePath($path);

                if (!in_array($mode , $mode_range)) {
                    ResourceRepository::create($url , $real_path , 'local', 0 , 0);
                }
            } else {
                if ($settings->disk === 'aliyun') {
                    $filename = AliyunOss::generateFilename($extension);
                    $res = AliyunOss::upload($settings->aliyun_bucket , $filename , $file->getPathname());
                    if ($res['code'] > 0) {
                        return self::error($res['message'] , $res['data'] , 500);
                    }
                    $url = $res['data'];
                    ResourceRepository::createAliyun($url , $settings->aliyun_bucket , 0 , 0);
                } else {
                    // todo 预留
                    $url = 'unknow';
                }
            }
            if (in_array($mode , $mode_range)) {
                $o_real_path = $real_path;
                $real_path = FileRepository::generateRealPathByWithPrefixRelativePath($path);
                $save_dir = FileRepository::dir('system', $dir);
                if (!file_exists($save_dir)) {
                    // 目录不存在则创建
                    mkdir($save_dir, 0755, true);
                }
                $image_processor = new ImageProcessor($save_dir);
                $real_path = $image_processor->compress($real_path, [
                    'mode' => $mode,
                    'ratio' => $param['r'],
                    'width' => $param['w'],
                    'height' => $param['h'],
                ], false);
                // 删除源文件
                unlink($o_real_path);
                $url = FileRepository::generateUrlByRealPath($real_path);

                if (!$is_upload_to_cloud || $settings->disk === 'local') {
                    ResourceRepository::create($url, $real_path, 'local', 0, 0);
                } else {
                    if ($settings->disk === 'aliyun') {
                        $filename = AliyunOss::generateFilename($extension);
                        $res = AliyunOss::upload($settings->aliyun_bucket, $filename, $real_path);
                        if ($res['code'] > 0) {
                            return self::error($res['message'], $res['data'], 500);
                        }
                        unlink($real_path);
                        $url = $res['data'];
                        ResourceRepository::createAliyun($url, $settings->aliyun_bucket, 0, 0);
                    } else {
                        // todo 预留
                        $url = 'unknow';
                    }
                }
            }
            Util::systemPowerDown();
            return self::success('' , $url);
        } catch(Exception $e) {
            Util::systemPowerDown();
            return self::error($e->getMessage() , $e->getTrace());
        }
    }

    // 上传视频
    public static function uploadVideo(Base $context , ?UploadedFile $file , array $param = [])
    {
        $validator = Validator::make($param , [
//            'file' => 'required|mimes:mp4,mov,mkv,avi,flv,rm,rmvb,ts,webm' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $mime_range = ['mp4','mov','mkv','avi','flv','rm','rmvb','ts','webm' , 'mpg' , '3gp' , 'wmv'];
        $extension = $file->getClientOriginalExtension();
        $extension = strtolower($extension);
        if (!in_array($extension , $mime_range)) {
            return self::error("不支持的格式【{$extension}】，当前支持的格式有：" . implode(',' , $mime_range));
        }
        try {
            Util::systemPowerUp();
            $dir        = date('Ymd');
            $path       = FileRepository::upload($file , $dir);
            $real_path  = FileRepository::generateRealPathByWithPrefixRelativePath($path);
            $url        = FileRepository::generateUrlByRelativePath($path);
            ResourceRepository::create($url , $real_path , 'local' , 0 , 0);
            Util::systemPowerDown();
            return self::success('' , $url);
        } catch(Exception $e) {
            Util::systemPowerDown();
            return self::error($e->getMessage() , $e->getTraceAsString());
        }
    }

    // 上传视频字幕
    public static function uploadSubtitle(Base $context , ?UploadedFile $file , array $param = [])
    {
        $validator = Validator::make($param , [
//            'file' => 'required|mimes:ass,idx,sub,srt,vtt,ssa' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $extension = $file->getClientOriginalExtension();
        $extension = strtolower($extension);
        $mime_range = ['ass' , 'idx' , 'sub' , 'srt' , 'vtt' , 'ssa'];
        if (!in_array($extension , $mime_range)) {
            return self::error("不支持的格式【{$extension}】，当前支持的格式有：" . implode(',' , $mime_range));
        }
        try {
            Util::systemPowerUp();
            $dir        = date('Ymd');
            $path       = FileRepository::upload($file , $dir);
            $real_path  = FileRepository::generateRealPathByWithPrefixRelativePath($path);
            $url        = FileRepository::generateUrlByRelativePath($path);
            ResourceRepository::create($url , $real_path , 'local' , 0 , 0);
            Util::systemPowerDown();
            return self::success('' , $url);
        } catch(Exception $e) {
            Util::systemPowerDown();
            return self::error($e->getMessage() , $e->getTraceAsString());
        }
    }

    // 上传文档|电子表格之类
    public static function uploadOffice(Base $context , ?UploadedFile $file , array $param = [])
    {
        $validator = Validator::make($param , [
            'file' => 'required|mimes:doc,docx,xls,xlsx' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        try {
            Util::systemPowerUp();
            $dir        = date('Ymd');
            $path       = FileRepository::upload($file , $dir);
            $real_path  = FileRepository::generateRealPathByWithPrefixRelativePath($path);
            $url        = FileRepository::generateUrlByRelativePath($path);
            ResourceRepository::create($url , $real_path , 'local' , 0 , 0);
            Util::systemPowerDown();
            return self::success('' , $url);
        } catch(Exception $e) {
            Util::systemPowerDown();
            return self::error($e->getMessage() , $e->getTraceAsString());
        }
    }


}
