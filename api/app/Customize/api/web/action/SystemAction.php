<?php


namespace App\Customize\api\web\action;

use App\Customize\api\web\model\SystemSettingsModel;
use App\Http\Controllers\api\web\Base;
class SystemAction extends Action
{
    public static function settings(Base $context , array $param = []): array
    {
        $friend_links = SystemSettingsModel::getValueByKey('friend_links');
        $friend_links = json_decode($friend_links , true);
        return self::success('' , [
            'friend_links' => $friend_links ,
        ]);
    }

}
