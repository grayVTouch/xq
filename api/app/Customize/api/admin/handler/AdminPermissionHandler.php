<?php


namespace App\Customize\api\admin\handler;


use App\Customize\api\admin\model\AdminPermissionModel;
use App\Customize\api\admin\model\Model;
use App\Customize\api\admin\repository\FileRepository;
use stdClass;
use Traversable;
use function core\convert_object;

class AdminPermissionHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);
        if ($deep) {
            $permission = $model->p_id ? AdminPermissionModel::find($model->p_id) : null;
            $permission = self::handle($permission , $with , false);
        } else {
            $permission = null;
        }
        $model->permission = $permission;

        // 特殊！仅该模型的资源文件在数据库写死！
        // 不允许程序改动，仅允许直接数据库修改


        return $model;
    }


}
