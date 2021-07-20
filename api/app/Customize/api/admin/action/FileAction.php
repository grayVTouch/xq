<?php


namespace App\Customize\api\admin\action;


use App\Customize\api\admin\util\FileUtil;
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
        try {
            Util::systemPowerUp();
            $dir        = date('Ymd');
            $path       = FileUtil::upload($file , $dir);
            $real_path  = FileUtil::generateRealPathByWithPrefixRelativePath($path);
            $url        = FileUtil::generateUrlByRelativePath($path);
            ResourceRepository::create($url , $real_path , 'local' , 1 , 0);
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
        $mode_range = my_config('business.mode_for_file');
        $validator = Validator::make($param , [
            'm' => ['sometimes' , Rule::in($mode_range)] ,
            'w' => 'sometimes|integer' ,
            'h' => 'sometimes|integer' ,
//            'file' => 'required|mimes:jpg,jpeg,png,gif,webp' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $mime_range = ['jpg','jpeg','png','gif','webp','bmp'];
        $extension = $file->getClientOriginalExtension();
        if (!in_array($extension , $mime_range)) {
            return self::error("不支持的格式【${$extension}】，当前支持的格式有：" . implode(',' , $mime_range));
        }
        try {
            Util::systemPowerUp();
            $dir        = date('Ymd');
            $path       = FileUtil::upload($file , $dir);
            $real_path  = FileUtil::generateRealPathByWithPrefixRelativePath($path);
            $url        = FileUtil::generateUrlByRelativePath($path);
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
            if (in_array($mode , $mode_range)) {
                $o_real_path        = $real_path;
                $real_path          = FileUtil::generateRealPathByWithPrefixRelativePath($path);
                $save_dir           = FileUtil::dir('system' , $dir);
                if (!file_exists($save_dir)) {
                    // 目录不存在则创建
                    mkdir($save_dir , 0755 , true);
                }
                $image_processor    = new ImageProcessor($save_dir);
                $real_path = $image_processor->compress($real_path , [
                    'mode'      => $mode ,
                    'ratio'     => $param['r'] ,
                    'width'     => $param['w'] ,
                    'height'    => $param['h'] ,
                ] , false);
                // 删除源文件
                unlink($o_real_path);
                $url = FileUtil::generateUrlByRealPath($real_path);
            }
            ResourceRepository::create($url , $real_path , 'local' , 1 , 0);
            Util::systemPowerDown();
            return self::success('' , $url);
        } catch(Exception $e) {
            Util::systemPowerDown();
            return self::error($e->getMessage() , $e->getTraceAsString());
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
            $path       = FileUtil::upload($file , $dir);
            $real_path  = FileUtil::generateRealPathByWithPrefixRelativePath($path);
            $url        = FileUtil::generateUrlByRelativePath($path);
            ResourceRepository::create($url , $real_path , 'local' , 1 , 0);
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
        $mime_range = ['ass' , 'idx' , 'sub' , 'srt' , 'vtt' , 'ssa'];
        if (!in_array($extension , $mime_range)) {
            return self::error("不支持的格式【${$extension}】，当前支持的格式有：" . implode(',' , $mime_range));
        }
        try {
            Util::systemPowerUp();
            $dir        = date('Ymd');
            $path       = FileUtil::upload($file , $dir);
            $real_path  = FileUtil::generateRealPathByWithPrefixRelativePath($path);
            $url        = FileUtil::generateUrlByRelativePath($path);
            ResourceRepository::create($url , $real_path , 'local' , 1 , 0);
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
            $path       = FileUtil::upload($file , $dir);
            $real_path  = FileUtil::generateRealPathByWithPrefixRelativePath($path);
            $url        = FileUtil::generateUrlByRelativePath($path);
            ResourceRepository::create($url , $real_path , 'local' , 1 , 0);
            Util::systemPowerDown();
            return self::success('' , $url);
        } catch(Exception $e) {
            Util::systemPowerDown();
            return self::error($e->getMessage() , $e->getTraceAsString());
        }
    }

}
