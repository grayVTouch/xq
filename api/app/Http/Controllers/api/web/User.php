<?php


namespace App\Http\Controllers\api\web;


use App\Customize\api\web\action\ImageSubjectWithAuthAction;
use App\Customize\api\web\action\LoginAction;
use App\Customize\api\web\action\UserAction;
use function api\web\error;
use function api\web\success;

class User extends Base
{

    // 删除收藏夹
    public function destroyCollectionGroup()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['collection_group_id'] = $param['collection_group_id'] ?? '';
        $res = UserAction::destroyCollectionGroup($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 删除收藏夹
    public function destroyAllCollectionGroup()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['collection_group_ids'] = $param['collection_group_ids'] ?? '';
        $res = UserAction::destroyAllCollectionGroup($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

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

    public function lessHistory()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['size'] = $param['size'] ?? '';
        $res = UserAction::lessHistory($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function histories()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['relation_type'] = $param['relation_type'] ?? '';
        $param['value'] = $param['value'] ?? '';
        $param['size'] = $param['size'] ?? '';
        $res = UserAction::histories($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 收藏夹列表-带有判断（判断某个事物是否存在于此）
    public function collectionGroupWithJudge()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['relation_type'] = $param['relation_type'] ?? '';
        $param['relation_id'] = $param['relation_id'] ?? '';
        $res = UserAction::collectionGroupWithJudge($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 收藏夹列表-带搜索
    public function collectionGroup()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['relation_type'] = $param['relation_type'] ?? '';
        $param['value'] = $param['value'] ?? '';
        $res = UserAction::collectionGroup($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }


    // 收藏 & 取消收藏
    public function collectionHandle()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['relation_type']    = $param['relation_type'] ?? '';
        $param['relation_id']    = $param['relation_id'] ?? '';
        $param['action']    = $param['action'] ?? '';
        $param['collection_group_id'] = $param['collection_group_id'] ?? '';
        $res = UserAction::collectionHandle($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 点赞 & 取消点赞
    public function praiseHandle()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['relation_type']    = $param['relation_type'] ?? '';
        $param['relation_id']    = $param['relation_id'] ?? '';
        $param['action']    = $param['action'] ?? '';
        $res = UserAction::praiseHandle($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function record()
    {
        $param = $this->request->post();
        $param['module_id']      = $param['module_id'] ?? '';
        $param['relation_type']    = $param['relation_type'] ?? '';
        $param['relation_id']    = $param['relation_id'] ?? '';
        $res = UserAction::record($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 创建并加入收藏夹
    public function createAndJoinCollectionGroup()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['name'] = $param['name'] ?? '';
        $param['relation_type']    = $param['relation_type'] ?? '';
        $param['relation_id']    = $param['relation_id'] ?? '';
        $res = UserAction::createAndJoinCollectionGroup($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 仅创建收藏夹
    public function createCollectionGroup()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['name'] = $param['name'] ?? '';
        $res = UserAction::createCollectionGroup($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    // 仅加入收藏夹
    public function joinCollectionGroup()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['collection_group_id'] = $param['collection_group_id'] ?? '';
        $param['relation_type']    = $param['relation_type'] ?? '';
        $param['relation_id']    = $param['relation_id'] ?? '';
        $res = UserAction::joinCollectionGroup($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }


    // 收藏夹列表
    public function lessRelationInCollection()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['collection_group_id'] = $param['collection_group_id'] ?? '';
        $param['size'] = $param['size'] ?? '';
        $res = UserAction::lessRelationInCollection($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function lessCollectionGroupWithCollection()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['collection_limit'] = $param['collection_limit'] ?? '';
        $param['relation_limit'] = $param['relation_limit'] ?? '';
        $res = UserAction::lessCollectionGroupWithCollection($this , $param);

        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
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

    public function destroyHistory()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['history_ids'] = $param['history_ids'] ?? '';
        $res = UserAction::destroyHistory($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function collections()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['collection_group_id'] = $param['collection_group_id'] ?? '';
        $param['relation_type'] = $param['relation_type'] ?? '';
        $param['value'] = $param['value'] ?? '';
        $param['size'] = $param['size'] ?? '';
        $res = UserAction::collections($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function destroyCollection()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['collection_id'] = $param['collection_id'] ?? '';
        $res = UserAction::destroyCollection($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function updateCollectionGroup()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['collection_group_id'] = $param['collection_group_id'] ?? '';
        $param['name'] = $param['name'] ?? '';
        $res = UserAction::updateCollectionGroup($this , $param);
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


    // 收藏夹列表-带搜索
    public function collectionGroupByUserId(int $user_id)
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['relation_type'] = $param['relation_type'] ?? '';
        $param['value'] = $param['value'] ?? '';
        $res = UserAction::collectionGroupByUserId($this , $user_id , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function collectionGroupInfo(int $collection_group_id)
    {
        $res = UserAction::collectionGroupInfo($this , $collection_group_id);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function myPraise()
    {
        $param = $this->request->query();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['relation_type'] = $param['relation_type'] ?? '';
        $param['value'] = $param['value'] ?? '';
        $param['size'] = $param['size'] ?? '';
        $res = UserAction::myPraise($this , $param);
        if ($res['code'] != 0) {
            return error($res['message'] , $res['data'] , $res['code']);
        }
        return success($res['message'] , $res['data']);
    }

    public function destroyMyPraise()
    {
        $param = $this->request->post();
        $param['module_id'] = $param['module_id'] ?? '';
        $param['praise_ids'] = $param['praise_ids'] ?? '';
        $res = UserAction::destroyMyPraise($this , $param);
        if ($res['code'] !== 0) {
            return error($res['message'] , $res['data'], $res['code']);
        }
        return success($res['message'] , $res['data']);
    }
}
