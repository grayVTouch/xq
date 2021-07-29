<?php


namespace App\Customize\api\admin\action;

use App\Customize\api\admin\handler\VideoProjectHandler;
use App\Customize\api\admin\job\VideoProjectResourceHandleJob;
use App\Customize\api\admin\model\CategoryModel;
use App\Customize\api\admin\model\ModuleModel;
use App\Customize\api\admin\model\RelationTagModel;
use App\Customize\api\admin\model\SystemSettingsModel;
use App\Customize\api\admin\model\TagModel;
use App\Customize\api\admin\model\UserModel;
use App\Customize\api\admin\model\VideoCompanyModel;
use App\Customize\api\admin\model\VideoSeriesModel;
use App\Customize\api\admin\model\VideoProjectModel;
use App\Customize\api\admin\repository\ResourceRepository;
use App\Customize\api\admin\repository\VideoProjectRepository;
use App\Http\Controllers\api\admin\Base;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function api\admin\get_form_error;
use function api\admin\my_config;
use function api\admin\my_config_keys;
use function api\admin\parse_order;
use function core\array_unit;
use function core\current_datetime;

class VideoProjectAction extends Action
{
    public static function index(Base $context , array $param = [])
    {
        $order = $param['order'] === '' ? [] : parse_order($param['order'] , '|');
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = VideoProjectModel::index([
            'module' ,
            'user' ,
            'category' ,
            'videoSeries' ,
            'videoCompany' ,
        ] , $param , $order , $size);
        $res = VideoProjectHandler::handlePaginator($res);
        foreach ($res->data as $v)
        {
            // 附加：模块
            VideoProjectHandler::module($v);
            // 附加：用户
            VideoProjectHandler::user($v);
            // 附加：分类
            VideoProjectHandler::category($v);
            // 附加：系列
            VideoProjectHandler::videoSeries($v);
            // 附加：公司
            VideoProjectHandler::videoCompany($v);
            // 附加：标签
            VideoProjectHandler::tags($v);
        }

        return self::success('' , $res);
    }

    public static function update(Base $context , $id , array $param = [])
    {
        $end_status_range   = my_config_keys('business.end_status_for_video_project');
        $status_range       = my_config_keys('business.status_for_video_project');
        $validator = Validator::make($param , [
            'name'              => 'required' ,
//            'release_date'      => 'sometimes|date_format:Y-m-d' ,
//            'end_date'          => 'sometimes|date_format:Y-m-d' ,
            'end_status'        => ['required' , Rule::in($end_status_range)] ,
            'status'            => ['required' , 'integer' , Rule::in($status_range)] ,
            'count'             => 'sometimes|integer' ,
            'weight'            => 'sometimes|integer' ,
            'video_series_id'   => 'sometimes|integer' ,
            'video_company_id'  => 'sometimes|integer' ,
            'user_id'           => 'required|integer' ,
            'min_index'           => 'required|integer' ,
            'max_index'           => 'required|integer' ,
            'module_id'         => 'required|integer' ,
            'category_id'       => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $video_project = VideoProjectModel::find($id);
        if (empty($video_project)) {
            return self::error('视频专题不存在' , '' , 404);
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        if (VideoProjectModel::findByModuleIdAndExcludeIdAndName($module->id , $video_project->id , $param['name'])) {
            return self::error('名称已经被使用');
        }
        $user = UserModel::find($param['user_id']);
        if (empty($user)) {
            return self::error('用户不存在');
        }
        if ($param['status'] !== '' && $param['status'] == -1 && $param['fail_reason'] === '') {
            return self::error('请提供失败原因');
        }
        $category = CategoryModel::find($param['category_id']);
        if (empty($category)) {
            return self::error('分类不存在');
        }
        $video_series = null;
        if (!empty($param['video_series_id'])) {
            $video_series = VideoSeriesModel::find($param['video_series_id']);
            if (empty($video_series)) {
                return self::error('视频系列不存在');
            }
        }
        $video_company = null;
        if (!empty($param['video_company_id'])) {
            $video_company = VideoCompanyModel::find($param['video_company_id']);
            if (empty($video_company)) {
                return self::error('视频制作公司不存在');
            }
        }
        $datetime               = current_datetime();
        $param['weight']        = $param['weight'] === '' ? 0 : $param['weight'];
        $param['updated_at']    = $datetime;
        $tags                   = $param['tags'] === '' ? [] : json_decode($param['tags'] , true);
        $param['release_date']  = empty($param['release_date']) ? null : $param['release_date'];
        $param['end_date']      = empty($param['end_date']) ? null : $param['end_date'];
        $param['release_year']  = empty($param['release_date']) ? null : date('Y' , strtotime($param['release_date']));
        $param['file_process_status'] = 0;
        try {
            DB::beginTransaction();
            VideoProjectModel::updateById($video_project->id , array_unit($param , [
                'name' ,
                'thumb' ,
                'score' ,
                'release_date' ,
                'end_date' ,
                'release_year' ,
                'end_status' ,
                'count' ,
                'description' ,
                'video_series_id' ,
                'video_company_id' ,
                'user_id',
                'status',
                'fail_reason',
                'file_process_status',
                'category_id' ,
                'module_id' ,
                'weight' ,
                'min_index' ,
                'max_index' ,
                'updated_at' ,
            ]));
            ResourceRepository::used($param['thumb']);
            if ($video_project->thumb !== $param['thumb']) {
                ResourceRepository::delete($video_project->thumb);
            }
            $my_tags = RelationTagModel::getByRelationTypeAndRelationId('image_project' , $video_project->id);
            foreach ($tags as $v)
            {
                foreach ($my_tags as $v1)
                {
                    if ($v1->tag_id === $v) {
                        DB::rollBack();
                        return self::error('' , [
                            'tags' => '存在重复标签: name: ' . $v1->name . '; id: ' . $v1->tag_id ,
                        ]);
                    }
                }
                $tag = TagModel::find($v);
                if (empty($tag)) {
                    DB::rollBack();
                    return self::error('存在不存在的标签' , '' , 404);
                }
                RelationTagModel::insertGetId([
                    'relation_type' => 'video_project' ,
                    'relation_id'   => $video_project->id ,
                    'tag_id'        => $tag->id ,
                    'name'          => $tag->name ,
                    'module_id'     => $tag->module_id ,
                    'updated_at'    => $datetime ,
                    'created_at'    => $datetime ,
                ]);
                // 针对该标签的计数要增加
                TagModel::updateById($tag->id , [
                    'count' => ++$tag->count
                ]);
            }
            DB::commit();
            // 执行任务
            VideoProjectResourceHandleJob::dispatch($video_project->id , $video_project->name);
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function store(Base $context , array $param = [])
    {
        $end_status_range   = my_config_keys('business.end_status_for_video_project');
        $status_range       = my_config_keys('business.status_for_video_project');
        $validator = Validator::make($param , [
            'name'              => 'required' ,
//            'release_date'      => 'sometimes|date_format:Y-m-d' ,
//            'end_date'          => 'sometimes|date_format:Y-m-d' ,
            'end_status'        => ['required' , Rule::in($end_status_range)] ,
            'status'            => ['required' , 'integer' , Rule::in($status_range)] ,
            'count'             => 'sometimes|integer' ,
            'weight'            => 'sometimes|integer' ,
            'video_series_id'   => 'sometimes|integer' ,
            'video_company_id'  => 'sometimes|integer' ,
            'user_id'           => 'required|integer' ,
            'module_id'         => 'required|integer' ,
            'category_id'       => 'required|integer' ,
            'min_index'           => 'required|integer' ,
            'max_index'           => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        if (VideoProjectModel::findByModuleIdAndName($module->id , $param['name'])) {
            return self::error('名称已经被使用');
        }
        $user = UserModel::find($param['user_id']);
        if (empty($user)) {
            return self::error('用户不存在');
        }
        if ($param['status'] !== '' && $param['status'] == -1 && $param['fail_reason'] === '') {
            return self::error('请提供失败原因');
        }
        $category = CategoryModel::find($param['category_id']);
        if (empty($category)) {
            return self::error('分类不存在');
        }
        $video_series = null;
        if (!empty($param['video_series_id'])) {
            $video_series = VideoSeriesModel::find($param['video_series_id']);
            if (empty($video_series)) {
                return self::error('视频系列不存在');
            }
        }
        $video_company = null;
        if (!empty($param['video_company_id'])) {
            $video_company = VideoCompanyModel::find($param['video_company_id']);
            if (empty($video_company)) {
                return self::error('视频制作公司不存在');
            }
        }
        $datetime = current_datetime();

        $param['file_process_status']   = 0;
        $param['release_date']  = empty($param['release_date']) ? null : $param['release_date'];
        $param['end_date']      = empty($param['end_date']) ? null : $param['end_date'];
        $param['release_year']  = empty($param['release_date']) ? null : date('Y' , strtotime($param['release_date']));
        $param['weight']        = $param['weight'] === '' ? 0 : $param['weight'];
        $param['updated_at']    = $datetime;
        $param['created_at']    = $datetime;
        $tags                   = $param['tags'] === '' ? [] : json_decode($param['tags'] , true);
        $param['disk'] = SystemSettingsModel::getValueByKey('disk');
        try {
            DB::beginTransaction();
            $id = VideoProjectModel::insertGetId(array_unit($param, [
                'name',
                'thumb',
                'score',
                'release_date',
                'end_date',
                'release_year',
                'end_status',
                'count',
                'description',
                'video_series_id',
                'video_company_id',
                'user_id',
                'status',
                'fail_reason',
                'file_process_status',
                'category_id',
                'module_id',
                'weight',
                'min_index' ,
                'max_index' ,
                'disk' ,
                'updated_at',
                'created_at',
            ]));
            ResourceRepository::used($param['thumb']);
            foreach ($tags as $v) {
                $tag = TagModel::find($v);
                if (empty($tag)) {
                    DB::rollBack();
                    return self::error('存在不存在的标签', '', 404);
                }
                RelationTagModel::insertGetId([
                    'relation_type' => 'video_project',
                    'relation_id'   => $id,
                    'tag_id'        => $tag->id,
                    'name'          => $tag->name,
                    'module_id'     => $tag->module_id,
                    'updated_at'    => $datetime ,
                    'created_at'    => $datetime ,

                ]);
                // 针对该标签的计数要增加
                TagModel::updateById($tag->id, [
                    'count' => ++$tag->count
                ]);
            }
            DB::commit();
            // 执行异步任务
            VideoProjectResourceHandleJob::dispatch($id);
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function show(Base $context , $id , array $param = [])
    {
        $res = VideoProjectModel::find($id);
        if (empty($res)) {
            return self::error('关联主体不存在' , '' , 404);
        }
        $res = VideoProjectHandler::handle($res);

        // 附加：模块
        VideoProjectHandler::module($res);
        // 附加：用户
        VideoProjectHandler::user($res);
        // 附加：分类
        VideoProjectHandler::category($res);
        // 附加：系列
        VideoProjectHandler::videoSeries($res);
        // 附加：公司
        VideoProjectHandler::videoCompany($res);
        // 附加：标签
        VideoProjectHandler::tags($res);

        return self::success('' , $res);
    }

    public static function destroy(Base $context , $id , array $param = [])
    {
        $res = VideoProjectModel::find($id);
        if (empty($res)) {
            return self::error('记录不存在' , '' , 404);
        }
        try {
            DB::beginTransaction();
            VideoProjectRepository::destroy($res->id);
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function destroyAll(Base $context , array $ids , array $param = [])
    {
        $res = VideoProjectModel::find($ids);
        try {
            DB::beginTransaction();
            foreach ($res as $v)
            {
                VideoProjectRepository::destroy($v->id);
            }
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // 删除单个标签
    public static function destroyTag(Base $context , array $param = []): array
    {
        $validator = Validator::make($param , [
            'video_project_id' => 'required|integer' ,
            'tag_id'           => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $count = RelationTagModel::delByRelationTypeAndRelationIdAndTagId('video_project' , $param['video_project_id'] , $param['tag_id']);
        return self::success('操作成功' , $count);
    }

    public static function search(Base $context , array $param = [])
    {
        if (empty($param['module_id'])) {
            return self::error('请提供 module_id');
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = VideoProjectModel::search($param['module_id'] , $param['value'] , $size);
        $res = VideoProjectHandler::handlePaginator($res);
        foreach ($res->data as $v)
        {
            // 附加：模块
            VideoProjectHandler::module($v);
            // 附加：用户
            VideoProjectHandler::user($v);
            // 附加：分类
            VideoProjectHandler::category($v);
            // 附加：系列
            VideoProjectHandler::videoSeries($v);
            // 附加：公司
            VideoProjectHandler::videoCompany($v);
            // 附加：标签
            VideoProjectHandler::tags($v);
        }
        return self::success('' , $res);
    }
}
