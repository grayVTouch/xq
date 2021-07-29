<?php


namespace App\Customize\api\admin\handler;


use App\Customize\api\admin\model\CategoryModel;
use App\Customize\api\admin\model\Model;
use App\Customize\api\admin\model\ModuleModel;
use App\Customize\api\admin\model\UserModel;
use stdClass;
use Traversable;
use function api\admin\get_config_key_mapping_value;
use function core\convert_object;

class CategoryHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        $model->__type__ = get_config_key_mapping_value('business.content_type' , $model->type);
        $model->__status__  = get_config_key_mapping_value('business.status_for_category' , $model->status);
        $model->__is_enabled__  = get_config_key_mapping_value('business.bool_for_int' , $model->is_enabled);

        return $model;
    }

    public static function user($model): void
    {
        if (empty($model)) {
            return ;
        }
        $user = property_exists($model , 'user') ? $model->user : UserModel::find($model->user_id);
        $user = UserHandler::handle($user);
        $model->user = $user;
    }

    public static function module($model): void
    {
        if (empty($model)) {
            return ;
        }
        $module = property_exists($model , 'module') ? $model->module : ModuleModel::find($model->module_id);
        $module = ModuleHandler::handle($module);
        $model->module = $module;
    }

    public static function parent($model , bool $deep = true): void
    {
        if (empty($model)) {
            return ;
        }
        $category = $model->p_id ?
            (
                property_exists($model , 'parent') ?
                    $model->parent :
                    CategoryModel::find($model->p_id))
            : null;
        if ($deep) {
            self::parent($category , $deep);
        }
        $model->parent = $category;
    }


}
