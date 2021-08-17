<?php


namespace App\Http\Controllers\api\web;


use App\Customize\api\web\action\SystemAction;
use function api\web\error;
use function api\web\success;

class System extends Base
{
    public function settings()
    {
        $res = SystemAction::settings($this);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }
}
