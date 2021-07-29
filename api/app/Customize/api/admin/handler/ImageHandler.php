<?php


namespace App\Customize\api\admin\handler;


use stdClass;
use function core\convert_object;

class ImageHandler extends Handler
{
    public static function handle($model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $model = convert_object($model);

        return $model;
    }

}
