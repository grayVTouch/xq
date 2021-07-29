<?php


namespace App\Customize\api\admin\handler;


use App\Customize\api\admin\model\RoleModel;
use App\Customize\api\admin\model\Model;
use stdClass;
use function core\convert_object;

class RoleHandler extends Handler
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
