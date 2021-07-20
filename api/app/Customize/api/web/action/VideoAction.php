<?php


namespace App\Customize\api\web\action;


use App\Customize\api\web\handler\RelationTagHandler;
use App\Customize\api\web\handler\VideoHandler;
use App\Customize\api\web\model\CategoryModel;
use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\PraiseModel;
use App\Customize\api\web\model\RelationTagModel;
use App\Customize\api\web\model\TagModel;
use App\Customize\api\web\model\UserVideoPlayRecordModel;
use App\Customize\api\web\model\VideoModel;
use App\Customize\api\web\model\VideoProjectModel;
use App\Customize\api\web\model\UserVideoProjectPlayRecordModel;
use App\Http\Controllers\api\web\Base;
use Core\Lib\Category;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function api\web\my_config;
use function api\web\my_config_keys;
use function api\web\parse_order;
use function api\web\user;
use function core\current_datetime;
use function core\object_to_array;

class VideoAction extends Action
{
    public static function incrementViewCount(Base $context , int $id , array $param = []): array
    {
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $video = VideoModel::find($id);
        if (empty($video)) {
            return self::error('视频不存在' , '' , 404);
        }
        if ($video->module_id !== $module->id) {
            return self::error('当前模块不对');
        }
        try {
            DB::beginTransaction();
            if ($video->type === 'pro') {
                VideoProjectModel::incrementByIdAndColumnAndStep($video->video_project_id , 'view_count' , 1);
            }
            VideoModel::incrementByIdAndColumnAndStep($video->id , 'view_count' , 1);
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function incrementPlayCount(Base $context , int $id , array $param = []): array
    {
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $video = VideoModel::find($id);
        if (empty($video)) {
            return self::error('视频不存在' , '' , 404);
        }
        if ($video->module_id !== $module->id) {
            return self::error('当前模块不对');
        }
        try {
            DB::beginTransaction();
            if ($video->type === 'pro') {
                VideoProjectModel::incrementByIdAndColumnAndStep($video->video_project_id , 'play_count' , 1);
            }
            VideoModel::incrementByIdAndColumnAndStep($video->id , 'play_count' , 1);
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function praiseHandle(Base $context , int $id , array $param = []): array
    {
        $action_range = my_config_keys('business.bool_for_int');
        $validator = Validator::make($param , [
            'module_id'     => 'required|integer' ,
            'action'        => ['required' , Rule::in($action_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        $video = VideoModel::find($id);
        if (empty($video)) {
            return self::error('视频不存在' , '' , 404);
        }
        $datetime = date('Y-m-d H:i:s');
        $user = user();
        if ($video->type === 'pro') {
            // 专题视频
            $relation_type = 'video_project';
            $relation_id = $video->video_project_id;
        } else {
            // 杂项视频
            $relation_type = 'video';
            $relation_id = $video->id;
        }
        try {
            DB::beginTransaction();
            // 视频专题
            if ($param['action'] == 1) {
                $praise = PraiseModel::findByModuleIdAndUserIdAndRelationTypeAndRelationId($module->id , $user->id , $relation_type , $relation_id);
                if (empty($praise)) {
                    PraiseModel::insertOrIgnore([
                        'module_id' => $module->id ,
                        'user_id' => $user->id ,
                        'relation_type' => $relation_type ,
                        'relation_id' => $relation_id ,
                        'created_at' => $datetime
                    ]);
                }
                VideoModel::incrementByIdAndColumnAndStep($video->id , 'praise_count' , 1);
                if ($relation_type === 'video_project') {
                    VideoProjectModel::incrementByIdAndColumnAndStep($relation_id , 'praise_count' , 1);
                }
            } else {
                PraiseModel::delByModuleIdAndUserIdAndRelationTypeAndRelationId($module->id , $user->id , $relation_type , $relation_id);
                VideoModel::decrementByIdAndColumnAndStep($video->id , 'praise_count' , 1);
                if ($relation_type === 'video_project') {
                    VideoProjectModel::decrementByIdAndColumnAndStep($relation_id , 'praise_count' , 1);
                }
            }
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function record(Base $context , int $id , array $param = []): array
    {
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $video = VideoModel::find($id);
        if (empty($video)) {
            return self::error('视频不存在' , '' , 404);
        }
        if ($video->module_id !== $module->id) {
            return self::error('当前模块不对');
        }
        if ($video->type === 'pro' && $param['index'] === '') {
            return self::error('视频索引尚未提供');
        }
        $timestamp = time();
        $datetime = date('Y-m-d H:i:s' , $timestamp);
        $param['played_duration'] = empty($param['played_duration']) ? 0 : $param['played_duration'];
        $param['ratio'] = number_format($param['played_duration'] / $video->duration , 2);
        try {
            if ($video->type === 'pro') {
                $user_video_project_play_record = UserVideoProjectPlayRecordModel::findByModuleIdAndUserIdAndVideoProjectId($module->id , user()->id , $video->video_project_id);
                if (empty($user_video_project_play_record)) {
                    UserVideoProjectPlayRecordModel::insertOrIgnore([
                        'module_id' => $module->id ,
                        'user_id' => user()->id ,
                        'video_project_id' => $video->video_project_id ,
                        'video_id' => $video->id ,
                        'index' => $param['index'] ,
                        'played_duration' => $param['played_duration'] ,
                        'definition' => $param['definition'] ,
                        'subtitle' => $param['subtitle'] ,
                        'ratio' => $param['ratio'] ,
                        'volume' => $param['volume'] ,
                        'date' => date('Y-m-d' , $timestamp) ,
                        'time' => date('H:i:s' , $timestamp) ,
                        'datetime' => date('Y-m-d H:i:s' , $timestamp) ,
                        'created_at' => $datetime ,
                    ]);
                } else {
                    UserVideoProjectPlayRecordModel::updateById($user_video_project_play_record->id , [
                        'video_id' => $video->id ,
                        'index' => $param['index'] ,
                        'played_duration' => $param['played_duration'] ,
                        'ratio' => $param['ratio'] ,
                        'volume' => $param['volume'] ,
                        'definition' => $param['definition'] ,
                        'subtitle' => $param['subtitle'] ,
                        'date' => date('Y-m-d' , $timestamp) ,
                        'time' => date('H:i:s' , $timestamp) ,
                        'datetime' => date('Y-m-d H:i:s' , $timestamp) ,
                    ]);
                }
            } else {
                // 非视频专题
                $user_video_play_record = UserVideoPlayRecordModel::findByModuleIdAndUserIdAndVideoId($module->id , user()->id , $video->id);
                if (empty($user_video_play_record)) {
                    UserVideoPlayRecordModel::insertOrIgnore([
                        'module_id' => $module->id ,
                        'user_id' => user()->id ,
                        'video_id' => $video->id ,
                        'played_duration' => $param['played_duration'] ,
                        'definition' => $param['definition'] ,
                        'subtitle' => $param['subtitle'] ,
                        'ratio' => $param['ratio'] ,
                        'volume' => $param['volume'] ,
                        'date' => date('Y-m-d' , $timestamp) ,
                        'time' => date('H:i:s' , $timestamp) ,
                        'datetime' => date('Y-m-d H:i:s' , $timestamp) ,
                        'created_at' => $datetime ,
                    ]);
                } else {
                    UserVideoPlayRecordModel::updateById($user_video_play_record->id , [
                        'played_duration' => $param['played_duration'] ,
                        'ratio' => $param['ratio'] ,
                        'volume' => $param['volume'] ,
                        'definition' => $param['definition'] ,
                        'subtitle' => $param['subtitle'] ,
                        'date' => date('Y-m-d' , $timestamp) ,
                        'time' => date('H:i:s' , $timestamp) ,
                        'datetime' => date('Y-m-d H:i:s' , $timestamp) ,
                    ]);
                }
            }
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function newest(Base $context , array $param = [])
    {
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = VideoModel::getNewestByFilterAndSize($param , $size);
        $res = VideoHandler::handleAll($res);
        foreach ($res as $v)
        {
            VideoHandler::tags($v);
        }
        return self::success('' , $res);
    }

    public static function hot(Base $context , array $param = [])
    {
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
        ]);

        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }

        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = VideoModel::getHotByFilterAndSize($param , $size);
        $res = VideoHandler::handleAll($res);
        foreach ($res as $v)
        {
            VideoHandler::tags($v);
        }
        return self::success('' , $res);
    }

    public static function hotTags(Base $context , array $param = [])
    {
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = RelationTagModel::hotTagsInVideoByFilterAndSize($param , $size);
        $res = RelationTagHandler::handleAll($res);
        return self::success('' , $res);
    }

    public static function hotTagsWithPager(Base $context , array $param = [])
    {
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = RelationTagModel::hotTagsWithPagerInVideoByValueAndFilterAndSize($param['value'] , $param , $size);
        $res = RelationTagHandler::handlePaginator($res);
        return self::success('' , $res);
    }

    public static function getByTagId(Base $context , array $param = [])
    {
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
            'tag_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $tag = TagModel::find($param['tag_id']);
        if (empty($tag)) {
            return self::error('标签不存在');
        }

        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = VideoModel::getByTagIdAndFilterAndSize($tag->id , $param , $size);
        $res = VideoHandler::handleAll($res);
        foreach ($res as $v)
        {
            VideoHandler::tags($v);
        }
        return self::success('' , $res);
    }

    public static function index(Base $context , array $param = [])
    {
        $mode_range = my_config('business.mode_for_video_project');

        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
            'mode'      => ['required' , Rule::in($mode_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $param['category_ids']   = $param['category_ids'] === '' ? [] : json_decode($param['category_ids'] , true);
        $param['video_series_ids']    = $param['video_series_ids'] === '' ? [] : json_decode($param['video_series_ids'] , true);
        $param['video_company_ids']    = $param['video_company_ids'] === '' ? [] : json_decode($param['video_company_ids'] , true);
        $param['tag_ids']        = $param['tag_ids'] === '' ? [] : json_decode($param['tag_ids'] , true);

        $order                   = $param['order'] === '' ? null : parse_order($param['order']);
        $size                   = $param['size'] === '' ? my_config('app.limit') : $param['size'];

        // 获取所有子类
        $categories         = CategoryModel::getAll();
        $categories         = object_to_array($categories);
        $tmp_category_ids   = [];

        foreach ($param['category_ids'] as $v)
        {
            $childrens          = Category::childrens($v , $categories , null , true , false);
            $ids                = array_column($childrens , 'id');
            $tmp_category_ids   = array_merge($tmp_category_ids , $ids);
        }

        $param['category_ids'] = array_unique($tmp_category_ids);
        $res = [];
        switch ($param['mode'])
        {
            case 'strict':
                $res = VideoModel::getWithPagerInStrictByFilterAndOrderAndSize($param , $order , $size);
                break;
            case 'loose':
                $res = VideoModel::getWithPagerInLooseByFilterAndOrderAndSize($param , $order , $size);
                break;
            default:
                return self::error('不支持的搜索模式，当前支持的模式有：' . implode(' , ' , $mode_range));
        }
        $res = VideoHandler::handlePaginator($res);
        foreach ($res->data as $v)
        {
            VideoHandler::tags($v);
        }
        return self::success('' , $res);
    }

    public static function category(Base $context , array $param = [])
    {
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $categories = CategoryModel::getByModuleIdAndType($module->id , 'video');
        $categories = object_to_array($categories);
        $categories = Category::childrens(0 , $categories , null , false , false);
        return self::success('' , $categories);
    }

    public static function show(Base $context , $id , array $param = [])
    {
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $res = VideoModel::find($id);
        if (empty($res)) {
            return self::error('视频不存在' , '' , 404);
        }
        $res = VideoHandler::handle($res);
        VideoHandler::isPraised($res);
        VideoHandler::isCollected($res);
        VideoHandler::tags($res);
        VideoHandler::user($res);
        VideoHandler::userPlayRecord($res);

        return self::success('' , $res);
    }

    public static function recommend(Base $context , $id , array $param = [])
    {
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $video = VideoModel::find($id);
        if (empty($video)) {
            return self::error('视频不存在' , '' , 404);
        }
        $param['exclude_id'] = $video->id;
        $param['category_id'] = $video->category_id;
        $size = empty($param['size']) ? my_config('app.limit') : $param['size'];
        $res = VideoModel::getRecommendByFieldAndFilterAndSize(null , $param , $size);
        $res = VideoHandler::handlePaginator($res);
        foreach ($res->data as $v)
        {
            VideoHandler::tags($v);
        }
        return self::success('' , $res);
    }
}
