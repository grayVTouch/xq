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
    public static function handle(?Model $model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $res = convert_object($model);

        // 我关注的人数量（关注数）
        $res->my_focus_user_count = FocusUserModel::countByUserId($res->id);
        // 关注我的人数量（粉丝数）
        $res->focus_me_user_count = FocusUserModel::countByFocusUserId($res->id);
        // 点赞数（我点赞的数量）
        $res->praise_count = PraiseModel::countByUserId($res->id);
        // 收藏数（收藏数量）
        $res->collect_count = CollectionModel::countByUserId($res->id);


        // 当前登录用户
        $user = user();

        if (!empty($user)) {
            if ($user->id === $res->id) {
                $res->focused = 0;
            } else {
                $res->focused = FocusUserModel::findByUserIdAndFocusUserId($user->id , $res->id) ? 1 : 0;
            }
        } else {
            $res->focused = 0;
        }
        $res->__sex__ = get_config_key_mapping_value('business.sex' , $res->sex);

        return $res;
    }

}
