<?php


namespace App\Customize\api\admin\action;

use App\Customize\api\admin\handler\DiskHandler;
use App\Customize\api\admin\model\ModuleModel;
use App\Customize\api\admin\model\DiskModel;
use App\Http\Controllers\api\admin\Base;
use Core\Lib\File;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function api\admin\get_form_error;
use function api\admin\my_config;
use function api\admin\my_config_keys;
use function api\admin\parse_order;
use function core\array_unit;
use function core\current_datetime;
use function core\format_capacity;
use function core\format_path;

class SystemDiskAction extends Action
{
    public static function index(Base $context , array $param = [])
    {
        $suitable_res = [];
        if (empty($param['parent_path'])) {
            $fastjson_jar_path  = __DIR__ . "/../java/jar/fastjson.jar";
            $out_path           = __DIR__ . "/../java/out";
            $source_java_file   = __DIR__ . "/../java/src/App.java";
            if (!file_exists($source_java_file)) {
                $command = "javac -cp \"{$fastjson_jar_path};\" -d \"{$out_path}\" \"{$source_java_file}\"";
                exec($command , $res , $status);
                if ($status > 0) {
                    return self::error("编译java源文件失败【status: {$status}】" , $res);
                }
            }
            $command = "java -cp \"{$fastjson_jar_path};{$out_path}\" App";
            // 列出盘符
            exec($command , $res , $status);
            if ($status > 0) {
                return self::error("获取盘符失败【status: {$status}】" , $res);
            }
            $res = implode('' , $res);
            $res = json_decode($res , true);
            $suitable_res = [];
            foreach ($res as $v)
            {
                $total_space = disk_total_space($v);
                $total_space = format_capacity($total_space);
                $free_space = disk_free_space($v);
                $free_space = format_capacity($free_space);
                $children = $res = File::getDirs($v , false);
                $suitable_res[] = [
                    'name'          => sprintf('%30s 【总容量：%30s】 【可用容量：%30s】' , $v , $total_space , $free_space) ,
                    'path'          => format_path($v) ,
                    'is_empty'      => (int) empty($children) ,
                ];
            }
        } else {
            // 列出子级
            $res = File::getDirs($param['parent_path'] , false);
            foreach ($res as $v)
            {
                $children = $res = File::getDirs($v , false);
                $suitable_res[] = [
                    'name' => $v ,
                    'path' => $v ,
                    'is_empty' => (int) empty($children) ,
                ];
            }
        }
        return self::success('' , $suitable_res);
    }
}
