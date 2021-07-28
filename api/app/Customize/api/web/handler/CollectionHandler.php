<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\CollectionGroupModel;
use App\Customize\api\web\model\CollectionModel;
use App\Customize\api\web\model\ImageModel;
use App\Customize\api\web\model\ImageProjectModel;
use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\UserModel;
use App\Customize\api\web\model\VideoModel;
use App\Customize\api\web\model\VideoProjectModel;
use App\Customize\api\web\repository\CollectionGroupRepository;
use App\Customize\api\web\model\Model;
use stdClass;
use function api\web\get_config_key_mapping_value;
use function core\convert_object;

class CollectionHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        $model->__relation_type__ = get_config_key_mapping_value('business.content_type' , $model->relation_type);

        return $model;
    }


    // 附加：模块
    public static function module($model): void
    {
        if (empty($model)) {
            return ;
        }
        $module = ModuleModel::find($model->module_id);
        ModuleHandler::handle($module);
        $model->module = $module;
    }

    // 附加：用户
    public static function user($model): void
    {
        if (empty($model)) {
            return ;
        }
        $user = UserModel::find($model->user_id);
        UserHandler::handle($user);
        $model->user = $user;
    }

    // 附加：收藏夹
    public static function collectionGroup($model): void
    {
        if (empty($model)) {
            return ;
        }
        $collection_group = CollectionGroupModel::find($model->collection_group_id);
        $collection_group = CollectionGroupHandler::handle($collection_group);
        $model->collection_group = $collection_group;
    }

    // 附加：关联事物
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
