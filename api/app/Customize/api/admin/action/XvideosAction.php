<?php


namespace App\Customize\api\admin\action;

use App\Customize\api\admin\job\M3U8DownloadJob;
use App\Http\Controllers\api\admin\Base;
use Core\Lib\M3U8;
use Illuminate\Support\Facades\Validator;
use function core\array_to_object;

class XvideosAction extends Action
{
    public static function parse(Base $context , array $param = []): array
    {
        $validator = Validator::make($param , [
            'src'       => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $m3u8 = new M3U8($param['src'] , [
            'proxy_pass' => $param['proxy_pass']
        ]);
        $definitions = $m3u8->getDefinitions();
        $definitions = array_keys($definitions);
        return self::success('' , $definitions);
    }

    public static function download(Base $context , array $param = []): array
    {
        $validator = Validator::make($param , [
            'save_dir'  => 'required' ,
            'src'       => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        if (!file_exists($param['save_dir'])) {
            return self::error('保存目录不存在');
        }
        preg_match('/^(https?:\/\/(.*)?)\/(.*)?\.m3u8.*$/' , $param['src'] , $matches);
        $url = $matches[1] ?? '';
        M3U8DownloadJob::dispatch(array_to_object([
            'save_dir' => $param['save_dir'] ,
            'url' => $url ,
            'src' => $param['src'] ,
            'filename' => $param['filename'] ,
            'definition' => $param['definition'] ,
            'proxy_pass' => $param['proxy_pass'] ,
        ]));
        return self::success('下载任务已经添加，请到保存目录查看（需要时间下载，请耐心等待）');
    }

}
