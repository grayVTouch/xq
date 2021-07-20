<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\HistoryModel;
use App\Customize\api\web\model\ImageModel;
use App\Customize\api\web\model\ImageProjectModel;
use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\UserModel;
use App\Customize\api\web\model\Model;
use App\Customize\api\web\model\VideoModel;
use App\Customize\api\web\model\VideoProjectModel;
use stdClass;
use function api\web\get_config_key_mapping_value;
use function core\convert_object;

class HistoryHandler extends Handler
{
    public static function handle(?Model $model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        $model->__relation_type__ = get_config_key_mapping_value('business.content_type' , $model->relation_type);

        return $model;
    }

    public static function module($model): void
    {
        if (empty($model)) {
            return ;
        }
        $module = ModuleModel::find($model->module_id);
        ModuleHandler::handle($module);
        $model->module = $module;
    }

    public static function user($model): void
    {
        if (empty($model)) {
            return ;
        }
        $user = UserModel::find($model->user_id);
        $user = UserHandler::handle($user);
        $model->user = $user;
    }

    public static function relation($model): void
    {
        if (empty($model)) {
            return ;
        }
        // 关联的主题
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
            case 'image':
                $relation = ImageModel::find($model->relation_id);
                $relation = ImageHandler::handle($relation);
                break;
            case 'video':
                $relation = VideoModel::find($model->relation_id);
                $relation = VideoHandler::handle($relation);
                break;
            default:
                $relation = null;
        }
        $model->relation = $relation;
    }


}
