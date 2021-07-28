<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\ImageProjectModel;
use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\RelationTagModel;
use App\Customize\api\web\model\VideoProjectModel;
use App\Customize\api\web\model\Model;
use stdClass;
use function core\convert_object;

class RelationTagHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $res = convert_object($model);

        return $res;
    }

    public static function relation($model): void
    {
        if (empty($model)) {
            return ;
        }
        switch ($model->relation_type)
        {
            case 'image_project':
                $relation = ImageProjectModel::find($model->relation_id);
                $relation = ImageProjectHandler::handle($relation);
                break;
            case 'video_project':
                $relation = VideoProjectModel::find($model->relation_id);
                $relation = VideoProjectHandler::handle($relation);
                break;
        }
        $model->relation = $relation;
    }

    // 模块
    public static function module($model): void
    {
        if (empty($model)) {
            return ;
        }
        $module = ModuleModel::find($model->module_id);
        $module = ModuleHandler::handle($module);

        $model->module = $module;
    }
}
