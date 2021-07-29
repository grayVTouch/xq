<?php


namespace App\Customize\api\admin\handler;

use App\Customize\api\admin\model\AdminPermissionModel;
use App\Customize\api\admin\model\RoleModel;
use App\Customize\api\admin\model\Model;
use Core\Lib\Category;
use stdClass;
use function api\admin\get_config_key_mapping_value;

use function core\convert_object;
use function core\object_to_array;

class AdminHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        $model->__sex__     = get_config_key_mapping_value('business.sex' , $model->sex);
        $model->__is_root__ = get_config_key_mapping_value('business.bool_for_int' , $model->is_root);

        return $model;
    }

    // 权限
    public static function permissions($model): void
    {
        if (empty($model)) {
            return ;
        }

        if ($model->is_root == 1) {
            $permissions = AdminPermissionModel::getAll();
        } else {
            $permissions = AdminPermissionModel::getByRoleId($model->role_id);
        }
        $permissions = AdminPermissionHandler::handleAll($permissions);
        $permissions = object_to_array($permissions);
        $permissions = Category::childrens(0 , $permissions , [
            'id'    => 'id' ,
            'p_id'  => 'p_id' ,
        ] , false , false);
        $model->permissions = $permissions;
    }

    // 角色
    public static function role($model): void
    {
        if (empty($model)) {
            return ;
        }
        $role = RoleModel::find($model->role_id);
        $role = RoleHandler::handle($role);
        $model->role = $role;
    }
}
