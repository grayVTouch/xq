<?php


namespace App\Customize\api\web\util;


use App\Customize\api\web\handler\DiskHandler;
use App\Customize\api\web\model\DiskModel;
use Core\Lib\File;
use Exception;
use Illuminate\Http\UploadedFile;
use stdClass;
use function api\web\my_config;
use function core\format_path;
use function core\random;

class FileUtil
{
    private static $disk = null;

    public static function disk(): ?stdClass
    {
        if (!empty($disk)) {
            return self::$disk;
        }
        $disk = DiskModel::findDefault();
        if (empty($disk)) {
            throw new Exception('请添加默认的可用存储路径');
        }
        $disk = DiskHandler::handle($disk);
        return self::$disk = $disk;
    }

    /**
     * 文件路径前缀
     *
     * @return  string
     * @throws Exception
     */
    public static function prefix(): string
    {
        $disk = self::disk();
        return $disk->prefix;
    }

    public static function path(): string
    {
        $disk = self::disk();
        return $disk->path;
    }

    // 生成随机的文件名
    public static function filename(): string
    {
        return date('YmdHis') . random(6 , 'letter' , true);
    }

    /**
     * 从目录前缀生成文件保存目录的绝对路径
     *
     * @param string $suffix
     * @return string
     * @throws Exception
     */
    public static function dir(string $type , string $suffix = ''): string
    {
        $dir_prefix = my_config('app.dir')[$type];
        $disk = $disk = self::disk();
        $suffix = empty($suffix) ? '' : trim($suffix , '/');
        $dir = $disk->path . '/' . $dir_prefix . '/' . $suffix;
        return format_path($dir);
    }

    /**
     * 保存上传文件
     *
     * @param UploadedFile|null $uploaded_file
     * @param string $dir
     * @return string|null 相对路径
     * @throws Exception
     */
    public static function upload(?UploadedFile $uploaded_file , string $dir = ''): ?string
    {
        $save_dir   = self::dir('system' , $dir);
        $source     = $uploaded_file->getRealPath();
        $extension  = $uploaded_file->getClientOriginalExtension();
        $filename   = self::filename();
        $file       = $filename . '.' . $extension;
        $target     = $save_dir . '/' . $file;
        File::mkdir($save_dir  , 0755 , true);
        if (!File::moveUploadedFile($source , $target)) {
            return false;
        }
        return self::generateWithPrefixRelativePath($target);
    }

    /**
     * 通过 带有路径前缀的相对路径 生成 实际可访问的绝对路径
     *
     * @param string $relative_path 带有路径前缀的相对路径
     * @return string
     * @throws Exception
     */
    public static function generateRealPathByWithPrefixRelativePath(string $relative_path = ''): string
    {
        if (empty($relative_path)) {
            return '';
        }
        $parse = explode('/' , $relative_path);
        if (empty($parse)) {
            throw new Exception('提供的路径不符合规范');
        }
        $prefix = $parse[0];
        $disk = $prefix === self::prefix() ? self::disk() : DiskModel::findByPrefix($prefix);
        if (empty($disk)) {
            throw new Exception('磁盘记录未找到');
        }
        $path = str_replace($prefix , '' , $relative_path);
        $path = ltrim($path , '/\\');
        $path = $disk->path . '/' . $path;
        return format_path($path);
    }

    /**
     * 通过给定的路径生成实际保存的绝对路径
     *
     * @param string $relative_path
     * @return string
     * @throws Exception
     */
    public static function generateRealPathByWithoutPrefixRelativePath(string $path = ''): string
    {
        $path = self::generateWithPrefixRelativePath($path);
        return self::generateRealPathByWithPrefixRelativePath($path);
    }

    // 根据用户提供的路径生成符合规则的相对路径
    public static function generateWithPrefixRelativePath(string $path = ''): string
    {
        if (empty($path)) {
            return '';
        }
        $prefix = self::prefix();
        $dir    = self::path();
        $relative_path = str_replace($dir , '' , $path);
        $relative_path = ltrim($relative_path , '/');
        return format_path($prefix . '/' . $relative_path);
    }

    /**
     * 从相对路径生成访问网络地址
     *
     * @param  string $path
     * @return string
     */
    public static function generateUrlByRelativePath(string $relative_path): string
    {
        $res_url        = my_config('app.res_url');
        $res_url        = rtrim($res_url , '/');
        $relative_path  = ltrim($relative_path , '/');
        return $res_url . '/' . $relative_path;
    }

    /**
     * 从绝对路径生成相对路径
     *
     * @param  string $path
     * @return string
     */
    public static function generateUrlByRealPath(string $real_path = ''): string
    {
        $real_path                      = format_path($real_path);
        $relative_path_with_prefix      = self::generateWithPrefixRelativePath($real_path);
        $res_url                        = my_config('app.res_url');
        $res_url                        = rtrim($res_url , '/');
        return $res_url . '/' . $relative_path_with_prefix;
    }

}
