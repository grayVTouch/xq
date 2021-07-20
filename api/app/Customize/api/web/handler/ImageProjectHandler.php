<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\CollectionModel;
use App\Customize\api\web\model\PraiseModel;
use App\Customize\api\web\model\RelationTagModel;
use App\Customize\api\web\model\CategoryModel;
use App\Customize\api\web\model\ImageModel;
use App\Customize\api\web\model\ImageProjectModel;
use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\ImageSubjectModel;
use App\Customize\api\web\model\UserModel;
use App\Customize\api\web\util\FileUtil;
use App\Customize\api\web\model\Model;
use Exception;
use stdClass;
use function api\web\get_config_key_mapping_value;
use function api\web\get_value;
use function api\web\user;
use function core\convert_object;
use function core\get_time_diff;

class ImageProjectHandler extends Handler
{
    /**
     * @param Model|null $model
     * @return stdClass|null
     * @throws Exception
     */
    public static function handle(?Model $model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        $model->__type__      = get_config_key_mapping_value('business.type_for_image_project', $model->type);
        $model->__status__    = get_config_key_mapping_value('business.status_for_image_project' , $model->status);

        $model->format_time = get_time_diff($model->created_at);

        return $model;
    }

    // 附加：收藏数量
    public static function collectCount($model): void
    {
        if (empty($model)) {
            return ;
        }
        // 收藏数量
        $model->collect_count = CollectionModel::countByModuleIdAndRelationTypeAndRelationId($model->module_id , 'image_project' , $model->id);
    }

    // 附加：是否收藏
    public static function isCollected($model): void
    {
        if (empty($model)) {
            return ;
        }
        if (empty(user())) {
            $model->is_collected = 0;
        } else {
            $model->is_collected = CollectionModel::findByModuleIdAndUserIdAndRelationTypeAndRelationId($model->module_id , user()->id , 'image_project' , $model->id) ? 1 : 0;
        }
    }

    // 附加：是否点赞
    public static function isPraised($model): void
    {
        if (empty($model)) {
            return ;
        }
        if (empty(user())) {
            $model->is_praised = 0;
        } else {
            $model->is_praised = PraiseModel::findByModuleIdAndUserIdAndRelationTypeAndRelationId($model->module_id , user()->id , 'image_project' , $model->id) ? 1 : 0;
        }
    }

    // 附加：模块
    public static function module($model): void
    {
        if (empty($model)) {
            return ;
        }
        $module = ModuleModel::find($model->module_id);
        $module = ModuleHandler::handle($module);

        $model->module = $module;
    }

    // 附加：用户
    public static function user($model): void
    {
        if (empty($model)) {
            return ;
        }
        $user = UserModel::find($model->user_id);
        $user = UserHandler::handle($user);

        $model->user = $user;
    }


    // 附加：分类
    public static function category($model): void
    {
        if (empty($model)) {
            return ;
        }
        $category = CategoryModel::find($model->category_id);
        $category = CategoryHandler::handle($category);
        $model->category = $category;
    }


    // 附加：图片专题
    public static function imageSubject($model): void
    {
        if (empty($model)) {
            return ;
        }
        if ($model->type === 'pro') {
            $image_subject = ImageSubjectModel::find($model->image_subject_id);
            $image_subject = ImageSubjectHandler::handle($image_subject);
        } else {
            $image_subject = null;
        }
        $model->image_subject = $image_subject;
    }

    // 附加：图片专题
    public static function images($model): void
    {
        if (empty($model)) {
            return ;
        }
        $images = ImageModel::getByImageProjectId($model->id);
        $images = ImageHandler::handleAll($images);

        $model->images = $images;
    }

    // 附加：标签
    public static function tags($model): void
    {
        if (empty($model)) {
            return ;
        }
        $tags = RelationTagModel::getByRelationTypeAndRelationId('image_project' , $model->id);
        $tags = RelationTagHandler::handleAll($tags);
        $model->tags = $tags;
    }

    public static function imageCount($model): void
    {
        if (empty($model)) {
            return ;
        }
        $model->image_count = ImageModel::countByImageProjectId($model->id);
    }

}
