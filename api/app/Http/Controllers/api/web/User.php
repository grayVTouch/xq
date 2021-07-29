<?php


namespace App\Http\Controllers\api\web;

use App\Customize\api\web\action\UserAction;
use function api\web\error;
use function api\web\success;

class User extends Base
{


    public function store()
    {
        $param = $this->request->post();
        $param['email']      = $param['email'] ?? '';
        $param['email_code']      = $param['email_code'] ?? '';
        $param['password']      = $param['password'] ?? '';
        $param['confirm_password']      = $param['confirm_password'] ?? '';
        $param['captcha_key']    = $param['captcha_key'] ?? '';
        $param['captcha_code']   = $param['captcha_code'] ?? '';
        $res = UserAction::store($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function info()
    {
        $res = UserAction::info($this);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function login()
    {
        $param = $this->request->post();
        $param['username']      = $param['username'] ?? '';
        $param['password']      = $param['password'] ?? '';
        $res = UserAction::login($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 更改密码
    public function updatePassword()
    {
        $param = $this->request->post();
        $param['email']         = $param['email'] ?? '';
        $param['email_code']    = $param['email_code'] ?? '';
        $param['password']      = $param['password'] ?? '';
        $param['confirm_password']      = $param['confirm_password'] ?? '';
        $res = UserAction::updatePassword($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function update()
    {
        $param = $this->request->post();
        $param['nickname'] = $param['nickname'] ?? '';
        $param['avatar'] = $param['avatar'] ?? '';
        $param['sex'] = $param['sex'] ?? '';
        $param['phone'] = $param['phone'] ?? '';
        $param['email'] = $param['email'] ?? '';
        $param['birthday'] = $param['birthday'] ?? '';
        $param['description'] = $param['description'] ?? '';
        $param['channel_thumb'] = $param['channel_thumb'] ?? '';
        $res = UserAction::update($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }


    public function localUpdate()
    {
        $param = $this->request->post();
        $param['nickname'] = $param['nickname'] ?? '';
        $param['avatar'] = $param['avatar'] ?? '';
        $param['sex'] = $param['sex'] ?? '';
        $param['phone'] = $param['phone'] ?? '';
        $param['email'] = $param['email'] ?? '';
        $param['birthday'] = $param['birthday'] ?? '';
        $param['description'] = $param['description'] ?? '';
        $param['channel_thumb'] = $param['channel_thumb'] ?? '';
        $res = UserAction::localUpdate($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function updatePasswordInLogged()
    {
        $param = $this->request->post();
        $param['old_password'] = $param['old_password'] ?? '';
        $param['password'] = $param['password'] ?? '';
        $param['confirm_password'] = $param['confirm_password'] ?? '';
        $res = UserAction::updatePasswordInLogged($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function focusHandle()
    {
        $param = $this->request->post();
        $param['user_id'] = $param['user_id'] ?? '';
        $param['action'] = $param['action'] ?? '';
        $res = UserAction::focusHandle($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function myFocusUser(int $user_id)
    {
        $param = $this->request->query();
        $param['size'] = $param['size'] ?? '';
        $res = UserAction::myFocusUser($this , $user_id , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function focusMeUser(int $user_id)
    {
        $param = $this->request->query();
        $param['size'] = $param['size'] ?? '';
        $res = UserAction::focusMeUser($this , $user_id , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function show(int $user_id)
    {
        $res = UserAction::show($this , $user_id);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }



}
