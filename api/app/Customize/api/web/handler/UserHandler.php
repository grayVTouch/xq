<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\CollectionModel;
use App\Customize\api\web\model\FocusUserModel;
use App\Customize\api\web\model\PraiseModel;
use App\Customize\api\web\model\TagModel;
use App\Customize\api\web\model\UserModel;
use App\Customize\api\web\repository\FileRepository;
use App\Customize\api\web\model\Model;
use stdClass;
use function api\web\get_config_key_mapping_value;
use function api\web\get_value;
use function api\web\user;
use function core\convert_object;

class UserHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        $model->__sex__ = get_config_key_mapping_value('business.sex' , $model->sex);

        return $model;
    }

    // 我关注的人数量（关注数）
    public static function myFocusUserCount($model)
    {
        if (empty($model)) {
            return ;
        }
        $model->my_focus_user_count = FocusUserModel::countByUserId($model->id);
    }

    // 关注我的人数量（粉丝数）
    public static function focusMeUserCount($model)
    {
        if (empty($model)) {
            return ;
        }
        $model->focus_me_user_count = FocusUserModel::countByFocusUserId($model->id);
    }

    // 点赞数（我点赞的数量）
    public static function praiseCount($model)
    {
        if (empty($model)) {
            return ;
        }
        $model->praise_count = PraiseModel::countByUserId($model->id);
    }

    // 收藏数（收藏数量）
    public static function collectCount($model)
    {
        if (empty($model)) {
            return ;
        }
        $model->collect_count = CollectionModel::countByUserId($model->id);
    }

    // 是否关注
    public static function focused($model)
    {
        if (empty($model)) {
            return ;
        }
        // 当前登录用户
        $user = user();

        if (!empty($user)) {
            if ($user->id === $model->id) {
                $is_focused = 0;
            } else {
                $is_focused = FocusUserModel::findByUserIdAndFocusUserId($user->id , $model->id) ? 1 : 0;
            }
        } else {
            $is_focused = 0;
        }
        $model->focused = $is_focused;
        $model->is_focused = $is_focused;
    }
}
