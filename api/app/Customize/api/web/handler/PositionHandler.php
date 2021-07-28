<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\PositionModel;
use App\Customize\api\web\model\Model;
use stdClass;
use function api\web\get_config_key_mapping_value;
use function api\web\get_value;
use function core\convert_object;

class PositionHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $res = convert_object($model);

        $res->__platform__ = get_config_key_mapping_value('business.platform' , $res->platform);

        return $res;
    }

}
