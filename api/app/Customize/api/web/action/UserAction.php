<?php


namespace App\Customize\api\web\action;

use App\Customize\api\web\handler\CollectionGroupHandler;
use App\Customize\api\web\handler\CollectionHandler;
use App\Customize\api\web\handler\FocusUserHandler;
use App\Customize\api\web\handler\HistoryHandler;
use App\Customize\api\web\handler\ImageHandler;
use App\Customize\api\web\handler\ImageProjectHandler;
use App\Customize\api\web\handler\PraiseHandler;
use App\Customize\api\web\handler\UserHandler;
use App\Customize\api\web\handler\UserVideoPlayRecordHandler;
use App\Customize\api\web\handler\UserVideoProjectPlayRecordHandler;
use App\Customize\api\web\handler\VideoHandler;
use App\Customize\api\web\handler\VideoProjectHandler;
use App\Customize\api\web\model\CollectionGroupModel;
use App\Customize\api\web\model\CollectionModel;
use App\Customize\api\web\model\EmailCodeModel;
use App\Customize\api\web\model\FocusUserModel;
use App\Customize\api\web\model\HistoryModel;
use App\Customize\api\web\model\ImageModel;
use App\Customize\api\web\model\ImageProjectModel;
use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\PraiseModel;
use App\Customize\api\web\model\UserModel;
use App\Customize\api\web\model\UserTokenModel;
use App\Customize\api\web\model\VideoModel;
use App\Customize\api\web\model\VideoProjectModel;
use App\Customize\api\web\repository\CollectionGroupRepository;
use App\Http\Controllers\api\web\Base;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Mews\Captcha\Facades\Captcha;
use function api\web\get_form_error;
use function api\web\my_config;
use function api\web\my_config_keys;
use function api\web\user;
use function core\array_unit;
use function core\current_datetime;
use function core\random;

class UserAction extends Action
{


    public static function store(Base $context , array $param = []): array
    {
        $validator = Validator::make($param , [
            'email' => 'required|email' ,
            'email_code' => 'required' ,
            'password' => 'required|min:6' ,
            'confirm_password' => 'required|min:6' ,
//            'captcha_code' => 'required|min:4' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
//        if (empty($param['captcha_key'])) {
//            return self::error('必要参数丢失【captcha_key】');
//        }
//        if (!Captcha::check_api($param['captcha_code'] , $param['captcha_key'])) {
//            return self::error([
//                'captcha_code' => '图形验证码错误',
//            ]);
//        }
        $user = UserModel::findByEmail($param['email']);
        if (!empty($user)) {
            return self::error('邮箱已经注册过，请登录');
        }
        if ($param['password'] !== $param['confirm_password']) {
            return self::error('两次输入的密码不一致');
        }
        // 检查验证码是否正确
        $email_code = EmailCodeModel::findByEmailAndType($param['email'] , 'register');
        if (empty($email_code)) {
            return self::error('请先发送邮箱验证码');
        }
        $timestamp = time();
        $code_duration = my_config('app.code_duration');
        $expired_timestamp = strtotime($email_code->send_at) + $code_duration;
        if ($email_code->used || $timestamp > $expired_timestamp) {
            return self::error('邮箱验证码已经失效，请重新发送');
        }
        if ($email_code->code !== $param['email_code']) {
            return self::error('验证码错误');
        }
        $token = random(32 , 'mixed' , true);
        $datetime = date('Y-m-d H:i:s' , time() + 7 * 24 * 3600);
        try {
            DB::beginTransaction();
            $id = UserModel::insertGetId([
                'username' => random(6 , 'letter' , true) ,
                'email' => $param['email'] ,
                'password' => Hash::make($param['password']) ,
                'last_time' => date('Y-m-d H:i:s'),
                'last_ip'   => $context->request->ip(),
            ]);
            UserTokenModel::insertGetId([
                'user_id' => $id ,
                'token' => $token ,
                'expired' => $datetime
            ]);
            EmailCodeModel::updateById($email_code->id , [
               'is_used' => 1 ,
            ]);
            DB::commit();
            return self::success('' , $token);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function login(Base $context , array $param = []): array
    {
        $validator = Validator::make($param , [
            'username' => 'required|min:6' ,
            'password' => 'required|min:6' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $user = UserModel::findByValueInUsernameOrEmailOrPhone($param['username']);
        if (empty($user)) {
            return self::error('用户不存在');
        }
        if (!Hash::check($param['password'] , $user->password)) {
            return self::error('密码错误');
        }
        $token = random(32 , 'mixed' , true);
        $datetime = date('Y-m-d H:i:s' , time() + 7 * 24 * 3600);
        try {
            DB::beginTransaction();
            UserTokenModel::insert([
                'user_id' => $user->id ,
                'token' => $token ,
                'expired' => $datetime
            ]);
            UserModel::updateById($user->id , [
                'last_time' => date('Y-m-d H:i:s'),
                'last_ip'   => $context->request->ip(),
            ]);
            DB::commit();
            return self::success('' , $token);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function info(Base $context , array $param = [])
    {
        $user = user();
        if (empty($user)) {
            return self::error('用户尚未登录' , '' , 401);
        }
        return self::success('' , $user);
    }

    public static function updatePassword(Base $context , array $param = [])
    {
        $validator = Validator::make($param , [
            'email' => 'required|min:6' ,
            'email_code' => 'required' ,
            'password' => 'required|min:6' ,
            'confirm_password' => 'required|min:6' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $user = UserModel::findByEmail($param['email']);
        if (empty($user)) {
            return self::error('邮箱尚未注册，请先注册');
        }
        if ($param['password'] !== $param['confirm_password']) {
            return self::error('两次输入的密码不一致');
        }
        // 检查验证码是否正确
        $email_code = EmailCodeModel::findByEmailAndType($user->email , 'password');
        if (empty($email_code)) {
            return self::error('请先发送邮箱验证码');
        }
        $timestamp = time();
        $code_duration = my_config('app.code_duration');
        $expired_timestamp = strtotime($email_code->send_at) + $code_duration;
        if ($email_code->used || $timestamp > $expired_timestamp) {
            return self::error('邮箱验证码已经失效，请重新发送');
        }
        if ($email_code->code !== $param['email_code']) {
            return self::error('验证码错误');
        }
        try {
            DB::beginTransaction();
            UserModel::updateById($user->id , [
                'password' => Hash::make($param['password'])
            ]);
            EmailCodeModel::updateById($email_code->id , [
                'is_used' => 1 ,
            ]);
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function update(Base $context , array $param = [])
    {
        $sex_range = my_config_keys('business.sex');
        $validator = Validator::make($param , [
            'sex' => ['required' , Rule::in($sex_range)] ,
            'email' => 'sometimes|email' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $user = user();
        UserModel::updateById($user->id , array_unit($param , [
            'nickname' ,
            'sex' ,
            'avatar' ,
            'phone' ,
            'email' ,
            'description' ,
            'birthday' ,
            'channel_thumb' ,
        ]));
        $user = UserModel::find($user->id);
        $user = UserHandler::handle($user);
        return self::success('' , $user);
    }


    public static function updatePasswordInLogged(Base $context , array $param = [])
    {
        $validator = Validator::make($param , [
            'old_password' => 'required' ,
            'password'      => 'required|min:6' ,
            'confirm_password' => 'required|min:6' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $user = user();
        if (!Hash::check($param['old_password'] , $user->password)) {
            return self::error('原密码错误');
        }
        if ($param['password'] !== $param['confirm_password']) {
            return self::error('两次输入的密码不一致');
        }
        $password = Hash::make($param['password']);
        UserModel::updateById($user->id , [
            'password' => $password
        ]);
        return self::success('操作成功');
    }



    public static function focusHandle(Base $context , array $param = []): array
    {
        $bool_range = my_config_keys('business.bool_for_int');
        $validator = Validator::make($param , [
            'user_id' => 'required|integer' ,
            'action' => ['required' , Rule::in($bool_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $focus_user = UserModel::find($param['user_id']);
        if (empty($focus_user)) {
            return self::error('用户不存在' , '' , 404);
        }
        $user = user();
        if ($user->id === $focus_user->id) {
            return self::error('禁止关注自己' , '' , 403);
        }
        if ($param['action'] == 1) {
            // 关注用户
            $res = FocusUserModel::findByUserIdAndFocusUserId($user->id , $focus_user->id);
            if ($res) {
                return self::error('您已经关注该用户' , 403);
            }
            FocusUserModel::insertGetId([
                'user_id' => $user->id ,
                'focus_user_id' => $focus_user->id ,
                'created_at' => current_datetime() ,
            ]);
        } else {
            // 取消关注
            FocusUserModel::delByUserIdAndFocusUserId($user->id , $focus_user->id);
        }
        return self::success('操作成功');
    }

    public static function myFocusUser(Base $context , int $user_id , array $param = []): array
    {
        $user = UserModel::find($user_id);
        if (empty($user)) {
            return self::error('用户不存在' , '' , 404);
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = FocusUserModel::getWithPagerByUserIdAndSize($user->id , $size);
        $res = FocusUserHandler::handlePaginator($res);
        return self::success('' , $res);
    }

    public static function focusMeUser(Base $context , int $user_id , array $param = []): array
    {
        $user = UserModel::find($user_id);
        if (empty($user)) {
            return self::error('用户不存在' , '' , 404);
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = FocusUserModel::getWithPagerByFocusUserIdAndSize($user->id , $size);
        $res = FocusUserHandler::handlePaginator($res);
        return self::success('' , $res);
    }

    public static function show(Base $context , int $user_id , array $param = []): array
    {
        $user = UserModel::find($user_id);
        if (empty($user)) {
            return self::error('用户不存在' , '' , 404);
        }
        $user = UserHandler::handle($user);
        UserHandler::myFocusUserCount($user);
        UserHandler::focusMeUserCount($user);
        UserHandler::praiseCount($user);
        UserHandler::collectCount($user);
        return self::success('' , $user);
    }

    // 局部更新
    public static function localUpdate(Base $context , array $param = [])
    {
        $sex_range = my_config_keys('business.sex');
        $validator = Validator::make($param , [
            'sex' => ['sometimes' , Rule::in($sex_range)] ,
            'email' => 'sometimes|email' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $user = user();
        $param['nickname']  = empty($param['nickname']) ? $user->nickname : $param['nickname'];
        $param['sex']       = $param['sex'] === '' ? $user->sex : $param['sex'];
        $param['avatar']    = $param['avatar'] === '' ? $user->avatar : $param['avatar'];
        $param['phone']     = $param['phone'] === '' ? $user->phone : $param['phone'];
        $param['email']     = $param['email'] === '' ? $user->email : $param['email'];
        $param['description']   = $param['description'] === '' ? $user->description : $param['description'];
        $param['birthday']      = empty($param['birthday']) ? $user->birthday : $param['birthday'];
        $param['channel_thumb'] = $param['channel_thumb'] === '' ? $user->channel_thumb : $param['channel_thumb'];
        UserModel::updateById($user->id , array_unit($param , [
            'nickname' ,
            'sex' ,
            'avatar' ,
            'phone' ,
            'email' ,
            'description' ,
            'birthday' ,
            'channel_thumb' ,
        ]));
        $user = UserModel::find($user->id);
        $user = UserHandler::handle($user);
        return self::success('' , $user);
    }
}
