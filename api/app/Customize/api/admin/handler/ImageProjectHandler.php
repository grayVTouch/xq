<?php


namespace App\Customize\api\admin\handler;


use App\Customize\api\admin\model\CategoryModel;
use App\Customize\api\admin\model\ImageModel;
use App\Customize\api\admin\model\ImageProjectModel;
use App\Customize\api\admin\model\ModuleModel;
use App\Customize\api\admin\model\RelationTagModel;
use App\Customize\api\admin\model\ImageSubjectModel;
use App\Customize\api\admin\model\UserModel;
use App\Customize\api\admin\repository\FileRepository;
use App\Customize\api\admin\model\Model;
use stdClass;
use function api\admin\get_config_key_mapping_value;

use function core\convert_object;

class ImageProjectHandler extends Handler
{
    public static function handle(?Model $model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        $model->__type__    = get_config_key_mapping_value('business.type_for_image_project' , $model->type);
        $model->__status__  = get_config_key_mapping_value('business.status_for_image_project' , $model->status);
        $model->__process_status__  = get_config_key_mapping_value('business.image_process_status' , $model->process_status);

        return $model;
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

    public static function module($model): void
    {
        if (empty($model)) {
            return ;
        }
        $module = ModuleModel::find($model->module_id);
        $module = ModuleHandler::handle($module);
        $model->module = $module;
    }

    public static function category($model): void
    {
        if (empty($model)) {
            return ;
        }
        $category = CategoryModel::find($model->category_id);
        $category = CategoryHandler::handle($category);
        $model->category = $category;
    }

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

    public static function images($model): void
    {
        if (empty($model)) {
            return ;
        }
        $images = ImageModel::getByImageProjectId($model->id);
        $images = ImageHandler::handleAll($images);
        $model->images = $images;
    }

    public static function tags($model): void
    {
        if (empty($model)) {
            return ;
        }
        $tags = RelationTagModel::getByRelationTypeAndRelationId('image_project' , $model->id);
        $model->tags = $tags;
    }

}
