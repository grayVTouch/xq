<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\CollectionGroupModel;
use App\Customize\api\web\model\CollectionModel;
use App\Customize\api\web\model\ImageProjectModel;
use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\UserModel;
use App\Customize\api\web\model\Model;
use stdClass;
use function api\web\user;
use function core\convert_object;

class CollectionGroupHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        // 该收藏夹内的物品数量
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

    public static function count($model): void
    {
        if (empty($model)) {
            return ;
        }
        $model->count = CollectionModel::countByCollectionGroupId($model->id);
    }

    // 附加：图片专题数量
    public static function countForImageProject($model): void
    {
        if (empty($model)) {
            return ;
        }
        $model->count_for_image_project = CollectionModel::countByCollectionGroupIdAndRelationType($model->id , 'image_project');
    }

    // 附加：收藏夹封面
    public static function thumb($model): void
    {
        if (empty($model)) {
            return ;
        }
        $thumb = '';
        $collection = CollectionModel::firstOrderIdAscByCollectionGroupId($model->id);
        if (!empty($collection)) {
            switch ($collection->relation_type)
            {
                case 'image_project':
                    $relation = ImageProjectModel::find($collection->relation_id);
                    if (!empty($relation)) {
                        $thumb = $relation->thumb;
                    }
                    break;
            }
        }
        $model->thumb = $thumb;
    }

    // 附加：是否存在于里面
    public static function isInside($model , $relation_type , $relation_id)
    {
        if (empty($model)) {
            return ;
        }
        $model->is_inside = CollectionModel::findByModuleIdAndUserIdAndCollectionGroupIdAndRelationTypeAndRelationId($model->module_id , $model->user_id , $model->id , $relation_type , $relation_id) ? 1 : 0;
    }

}
