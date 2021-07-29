<?php


namespace App\Customize\api\admin\handler;

use App\Customize\api\admin\model\Model;
use App\Customize\api\admin\model\ModuleModel;
use App\Customize\api\admin\model\UserModel;
use stdClass;
use function api\admin\get_config_key_mapping_value;

use function core\convert_object;

class ModuleHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        $model->__is_enabled__ = get_config_key_mapping_value('business.bool_for_int' , $model->is_enabled);
        $model->__is_auth__ = get_config_key_mapping_value('business.bool_for_int' , $model->is_auth);
        $model->__is_default__ = get_config_key_mapping_value('business.bool_for_int' , $model->is_default);

        return $model;
    }

}
