<?php


namespace App\Customize\api\admin\handler;


use App\Customize\api\admin\model\ImageModel;
use App\Customize\api\admin\repository\FileRepository;
use App\Customize\api\admin\model\Model;
use stdClass;
use function core\convert_object;

class ImageHandler extends Handler
{
    public static function handle(?Model $model): ?stdClass
    {
        if (empty($model)) {
            return null;
        }
        $res = convert_object($model);

        return $res;
    }

}
