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

class SystemSettingsHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        return $model;
    }

}
