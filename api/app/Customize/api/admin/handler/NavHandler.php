<?php


namespace App\Customize\api\admin\handler;


use App\Customize\api\admin\model\CategoryModel;
use App\Customize\api\admin\model\Model;
use App\Customize\api\admin\model\ModuleModel;
use App\Customize\api\admin\model\NavModel;
use stdClass;
use function api\admin\get_config_key_mapping_value;

use function core\convert_object;

class NavHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }

        $model = convert_object($model);

        $model->__is_enabled__ = get_config_key_mapping_value('business.bool_for_int' , $model->is_enabled);
        $model->__type__ = get_config_key_mapping_value('business.type_for_nav' , $model->type);

        return $model;
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
        $nav = $model->p_id ?
            (
            property_exists($model , 'parent') ?
                $model->parent :
                NavModel::find($model->p_id))
            : null;
        if ($deep) {
            self::parent($nav , $deep);
        }
        $model->parent = $nav;
    }

    public static function category($model): void
    {
        $category = property_exists($model , 'category') ? $model->category : CategoryModel::find($model->value);
        $category = CategoryHandler::handle($category);
        $model->category = $category;
    }

}
