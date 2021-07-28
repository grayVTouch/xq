<?php


namespace App\Customize\api\web\action;

use App\Customize\api\web\handler\NavHandler;
use App\Customize\api\web\model\NavModel;
use App\Http\Controllers\api\web\Base;
use Core\Lib\Category;
use function core\object_to_array;

class NavAction extends Action
{
    public static function all(Base $context , array $param = [])
    {
        if (empty($param['module_id'])) {
            return self::error('请提供 module_id');
        }
        $res = NavModel::getAllByRelationAndModuleId([] , $param['module_id']);
        $res = NavHandler::handleAll($res);
        $res = object_to_array($res);
        $res = Category::childrens(0 , $res , null , false ,false);
        return self::success('' , $res);
    }
}
