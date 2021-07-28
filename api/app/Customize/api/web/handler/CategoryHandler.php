<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\CategoryModel;
use App\Customize\api\web\model\Model;
use App\Customize\api\web\model\ModuleModel;
use stdClass;
use Traversable;
use function core\convert_object;

class CategoryHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        return $model;
    }

    // 附加：上级
    public static function parent($model): void
    {
        if (empty($model)) {
            return ;
        }
        $parent = $model->p_id ? CategoryModel::find($model->p_id) : null;
        $parent = self::handle($parent);
        $model->parent = $parent;
    }

}
