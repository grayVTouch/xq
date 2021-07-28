<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\ImageSubjectModel;
use App\Customize\api\web\model\UserModel;
use App\Customize\api\web\repository\FileRepository;
use App\Customize\api\web\model\Model;
use stdClass;
use function core\convert_object;

class ImageSubjectHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        $model->__attr__ = empty($model->attr) ? [] : json_decode($model->attr , true);

        return $model;
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




}
