<?php


namespace App\Customize\api\admin\handler;


use App\Customize\api\admin\model\TagModel;
use App\Customize\api\admin\model\UserModel;
use App\Customize\api\admin\repository\FileRepository;
use App\Customize\api\admin\model\Model;
use stdClass;
use function api\admin\get_config_key_mapping_value;

use function core\convert_object;

class UserHandler extends Handler
{
    public static function handle(?Model $model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        $model->__is_system__   = get_config_key_mapping_value('business.bool_for_int' , $model->is_system);
        $model->__sex__         = get_config_key_mapping_value('business.sex' , $model->sex);

        return $model;
    }

}
