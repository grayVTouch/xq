<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\UserModel;
use App\Customize\api\web\model\UserVideoProjectPlayRecordModel;
use App\Customize\api\web\model\VideoSeriesModel;
use App\Customize\api\web\model\VideoCompanyModel;
use App\Customize\api\web\model\CollectionModel;
use App\Customize\api\web\model\PraiseModel;
use App\Customize\api\web\model\RelationTagModel;
use App\Customize\api\web\model\CategoryModel;
use App\Customize\api\web\model\VideoModel;
use App\Customize\api\web\model\VideoProjectModel;
use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\repository\FileRepository;
use App\Customize\api\web\model\Model;
use stdClass;
use function api\web\get_config_key_mapping_value;
use function api\web\get_value;
use function api\web\user;
use function core\convert_object;

class UserVideoPlayRecordHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $res = convert_object($model);


        return $res;
    }

    public static function video($model): void
    {
        if (empty($model)) {
            return ;
        }
        $video = VideoModel::find($model->video_id);
        $video = VideoHandler::handle($video);

        $model->video = $video;
    }
}
