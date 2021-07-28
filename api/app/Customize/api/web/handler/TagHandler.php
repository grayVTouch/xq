<?php


namespace App\Customize\api\web\handler;


use App\Customize\api\web\model\TagModel;
use App\Customize\api\web\model\Model;
use stdClass;
use function core\convert_object;

class TagHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $res = convert_object($model);

        return $res;
    }

}
