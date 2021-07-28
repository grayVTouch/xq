<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\Model;
use App\Customize\api\web\model\NavModel;
use stdClass;
use Traversable;
use function core\convert_object;

class NavHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $res = convert_object($model);

        return $res;
    }

    public static function parent($model): void
    {
        if (empty($model)) {
            return ;
        }
        if ($model->p_id > 0) {
            $parent = property_exists($model , 'parent') ? $model->parent : NavModel::find($model->p_id);
        } else {
            $parent = null;
        }
        $parent = self::handle($parent);
        $model->parent = $parent;
    }

}
