<?php


namespace App\Customize\api\web\action;


use App\Customize\api\web\handler\CollectionGroupHandler;
use App\Customize\api\web\handler\CollectionHandler;
use App\Customize\api\web\handler\ImageHandler;
use App\Customize\api\web\handler\ImageProjectHandler;
use App\Customize\api\web\handler\UserVideoPlayRecordHandler;
use App\Customize\api\web\handler\UserVideoProjectPlayRecordHandler;
use App\Customize\api\web\handler\VideoHandler;
use App\Customize\api\web\handler\VideoProjectHandler;
use App\Customize\api\web\model\CollectionGroupModel;
use App\Customize\api\web\model\CollectionModel;
use App\Customize\api\web\model\ImageModel;
use App\Customize\api\web\model\ImageProjectModel;
use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\UserModel;
use App\Customize\api\web\model\VideoModel;
use App\Customize\api\web\model\VideoProjectModel;
use App\Customize\api\web\repository\Repository;
use App\Http\Controllers\api\web\Base;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function api\web\my_config;
use function api\web\my_config_keys;
use function api\web\user;
use function core\current_datetime;

class CollectionGroupAction extends Action
{

    public static function destroy(Base $context , int $id , array $param = []): array
    {
        $param['collection_group_ids'] = [$param['collection_group_id']];
        return self::destroyAll($context , [$id] , $param);
    }

    public static function destroyAll(Base $context , array $ids , array $param = []): array
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
        if (empty($ids)) {
            return self::error('请提供待删除的收藏夹');
        }
        $user = user();
        try {
            DB::beginTransaction();
            CollectionGroupModel::delByModuleIdAndUserIdAndIds($module->id , $user->id , $ids);
            CollectionModel::delByModuleIdAndUserIdAndCollectionGroupIds($module->id , $user->id , $ids);
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public static function getWithJudge(Base $context , array $param = []): array
    {
        $type_range = my_config_keys('business.content_type');
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
            'relation_type' => ['required' , Rule::in($type_range)] ,
            'relation_id' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        switch ($param['relation_type'])
        {
            case 'image_project':
                $relation = ImageProjectModel::find($param['relation_id']);
                break;
            case 'video_project':
                $relation = VideoProjectModel::find($param['relation_id']);
                break;
            case 'image':
                $relation = ImageModel::find($param['relation_id']);
                break;
            case 'video':
                $relation = VideoModel::find($param['relation_id']);
                break;
            default:
                $relation = null;
        }
        if (empty($relation)) {
            return self::error('关联事物不存在');
        }
        $user = user();
        $res = CollectionGroupModel::getByModuleIdAndUserId($module->id , $user->id);
        $res = CollectionGroupHandler::handleAll($res);
        // 附加：是否存在于里面
        foreach ($res as $v)
        {
            // 附加：累计数量
            CollectionGroupHandler::count($v);
            // 是否在里面
            CollectionGroupHandler::isInside($v , $param['relation_type'] , $relation->id);
        }
        return self::success('' , $res);
    }

    public static function index(Base $context , array $param = []): array
    {
        $relation_type_range = my_config_keys('business.content_type');
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
            'relation_type' => ['sometimes' , Rule::in($relation_type_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        $user = user();
        $res = CollectionGroupModel::getByModuleIdAndUserIdAndValue($module->id , $user->id ,  $param['value']);
        $res = CollectionGroupHandler::handleAll($res);
        if ($param['relation_type'] === 'image_project') {
            array_walk($res , function ($v) use($param){
                // 附加：累计数量
                CollectionGroupHandler::count($v);
                // 附加：封面
                CollectionGroupHandler::thumb($v);
                // 附加：图片专题数量
                CollectionGroupHandler::countForImageProject($v);
            });
        }
        return self::success('' , $res);
    }

    public static function collectOrCancel(Base $context , int $id , array $param = [])
    {
        $relation_type_range = my_config_keys('business.content_type');
        $action_range = my_config_keys('business.bool_for_int');
        $validator = Validator::make($param , [
            'module_id'             => 'required|integer' ,
            'action'                => ['required' , Rule::in($action_range)] ,
            'relation_type' => ['required' , Rule::in($relation_type_range)] ,
            'relation_id' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        $collection_group = CollectionGroupModel::find($id);
        if (empty($collection_group)) {
            return self::error('收藏夹不存在' , '' , 404);
        }
        $user = user();
        switch ($param['relation_type'])
        {
            case 'image_project':
                $relation = ImageProjectModel::find($param['relation_id']);
                break;
            case 'video_project':
                $relation = VideoProjectModel::find($param['relation_id']);
                break;
            case 'image':
                $relation = ImageModel::find($param['relation_id']);
                break;
            case 'video':
                $relation = VideoModel::find($param['relation_id']);
            default:
        }
        if (empty($relation)) {
            return self::error('关联事物不存在');
        }
        try {
            DB::beginTransaction();
            if ($param['action'] == 1) {
                // 收藏
                $res = CollectionModel::findByModuleIdAndUserIdAndCollectionGroupIdAndRelationTypeAndRelationId($module->id , $user->id , $collection_group->id , $param['relation_type'] , $relation->id);
                if (empty($res)) {
                    CollectionModel::insertOrIgnore([
                        'module_id' => $module->id ,
                        'user_id' => $user->id ,
                        'collection_group_id' => $collection_group->id ,
                        'relation_type' => $param['relation_type'] ,
                        'relation_id' => $relation->id ,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
                switch ($param['relation_type'])
                {
                    case 'image_project':
                        ImageProjectModel::incrementByIdAndColumnAndStep($relation->id , 'collect_count' , 1);
                        break;
                    case 'video_project':
                        VideoProjectModel::incrementByIdAndColumnAndStep($relation->id , 'collect_count' , 1);
                        break;
                    case 'image':
                        ImageModel::incrementByIdAndColumnAndStep($relation->id , 'collect_count' , 1);
                        break;
                    case 'video':
                        VideoModel::incrementByIdAndColumnAndStep($relation->id , 'collect_count' , 1);
                    default:
                }
            } else {
                // 取消收藏
                CollectionModel::delByModuleIdAndUserIdAndCollectionGroupIdAndRelationTypeAndRelationId($module->id , $user->id , $collection_group->id , $param['relation_type'] , $relation->id);
                switch ($param['relation_type'])
                {
                    case 'image_project':
                        ImageProjectModel::decrementByIdAndColumnAndStep($relation->id , 'collect_count' , 1);
                        break;
                    case 'video_project':
                        VideoProjectModel::decrementByIdAndColumnAndStep($relation->id , 'collect_count' , 1);
                        break;
                    case 'image':
                        ImageModel::decrementByIdAndColumnAndStep($relation->id , 'collect_count' , 1);
                        break;
                    case 'video':
                        VideoModel::decrementByIdAndColumnAndStep($relation->id , 'collect_count' , 1);
                    default:
                }
            }
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public static function createAndJoin(Base $context , array $param = [])
    {
        $relation_type_range = my_config_keys('business.content_type');
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
            'relation_type' => ['required' , Rule::in($relation_type_range)] ,
            'relation_id' => 'required|integer' ,
            'name'      => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        switch ($param['relation_type'])
        {
            case 'image_project':
                $relation = ImageProjectModel::find($param['relation_id']);
                break;
            case 'video_project':
                $relation = VideoProjectModel::find($param['relation_id']);
                break;
            case 'image':
                $relation = ImageModel::find($param['relation_id']);
                break;
            case 'video':
                $relation = VideoModel::find($param['relation_id']);
                break;
            default:
                $relation = null;
        }
        // https://static-hw.xvideos.com/v3
        if (empty($relation)) {
            return self::error('关联的事物不存在' , '' , 404);
        }
        if ($module->id !== $relation->module_id) {
            return self::error('禁止记录不同模块的内容' , '' , 403);
        }
        $user = user();
        $collection_group = CollectionGroupModel::findByModuleIdAndUserIdAndName($module->id , $user->id , $param['name']);
        if (!empty($collection_group)) {
            return self::error('收藏夹已经存在');
        }
        try {
            DB::beginTransaction();
            $id = CollectionGroupModel::insertGetId([
                'module_id' => $module->id ,
                'user_id' => $user->id ,
                'name' => $param['name'] ,
                'created_at' => current_datetime() ,
            ]);
            CollectionModel::insertGetId([
                'module_id' => $module->id ,
                'user_id' => $user->id ,
                'collection_group_id' => $id ,
                'relation_type' => $param['relation_type'] ,
                'relation_id' => $relation->id ,
                'created_at' => current_datetime() ,
            ]);
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function store(Base $context , array $param = [])
    {
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
            'name'      => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        $user = user();
        $collection_group = CollectionGroupModel::findByModuleIdAndUserIdAndName($module->id , $user->id , $param['name']);
        if (!empty($collection_group)) {
            return self::error('收藏夹已经存在');
        }
        $res = CollectionGroupModel::insertGetId([
            'module_id' => $module->id ,
            'user_id' => $user->id ,
            'name' => $param['name'] ,
            'created_at' => current_datetime() ,
        ]);
        return self::success('' , $res);
    }

    public static function join(Base $context , int $id , array $param = [])
    {
        $relation_type_range = my_config_keys('business.content_type');
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
            'relation_type' => ['required' , Rule::in($relation_type_range)] ,
            'relation_id' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        switch ($param['relation_type'])
        {
            case 'image_project':
                $relation = ImageProjectModel::find($param['relation_id']);
                break;
            default:
                $relation = null;
        }
        if (empty($relation)) {
            return self::error('关联的事物不存在' , '' , 404);
        }
        if ($module->id !== $relation->module_id) {
            return self::error('禁止记录不同模块的内容' , '' , 403);
        }
        $user = user();
        $collection_group = CollectionGroupModel::find($id);
        if (empty($collection_group)) {
            return self::error('收藏夹不存在' , '' , 404);
        }
        CollectionModel::insertGetId([
            'module_id' => $module->id ,
            'user_id' => $user->id ,
            'collection_group_id' => $collection_group->id ,
            'relation_type' => $param['relation_type'] ,
            'relation_id' => $relation->id ,
            'created_at' => current_datetime() ,
        ]);
        $collection_group = CollectionGroupHandler::handle($collection_group);
        CollectionGroupHandler::isInside($collection_group , $param['relation_type'] , $relation->id);
        return self::success('操作成功');
    }

    public static function lessCollection(Base $context , int $id , array $param = [])
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
        $collection_group = CollectionGroupModel::find($id);
        if (empty($collection_group)) {
            return self::error('收藏夹不存在' , '' , 404);
        }
        $size = $param['size'] ? $param['size'] : my_config('app.limit');
        $user = user();
        $res = CollectionModel::getByModuleIdAndUserIdAndCollectionGroupIdAndSize($module->id , $user->id , $collection_group->id , $size);
        $res = CollectionHandler::handleAll($res);
        foreach ($res as $v)
        {
            CollectionHandler::module($v);
            CollectionHandler::user($v);
            CollectionHandler::collectionGroup($v);
            CollectionHandler::relation($v);
        }
        return self::success('' , $res);
    }

    public static function getWithCollection(Base $context , array $param = [])
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
        $collection_group_limit = $param['collection_group_limit'] ? $param['collection_group_limit'] : my_config('app.limit');
        $collection_limit = $param['collection_limit'] ? $param['collection_limit'] : my_config('app.limit');
        $user = user();
        $total_collection_group = CollectionGroupModel::countByModuleIdAndUserId($module->id , $user->id);
        $collection_group = CollectionGroupModel::getByModuleIdAndUserIdAndSize($module->id , $user->id , $collection_group_limit);
        $collection_group = CollectionGroupHandler::handleAll($collection_group);
        $all_collections = [];
        foreach ($collection_group as $v)
        {
            // 附加：累计数量
            CollectionGroupHandler::count($v);
            $collections = CollectionModel::getByModuleIdAndUserIdAndCollectionGroupIdAndSize($module->id , $user->id , $v->id , $collection_limit);
            $collections = CollectionHandler::handleAll($collections);
            $all_collections = array_merge($all_collections , $collections);
            $v->collections = $collections;
        }
        Repository::withRelationUsePreloadByModuleAndCollectionSupportCollection($module , $all_collections);
        return self::success('' , [
            'total_collection_group'    => $total_collection_group ,
            'collection_groups'         => $collection_group ,
        ]);
    }


    public static function collections(Base $context , int $id , array $param = []): array
    {
        $relation_type_range = my_config_keys('business.content_type');
        $validator = Validator::make($param , [
            'module_id' => 'required' ,
            'relation_type' => ['sometimes' , Rule::in($relation_type_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        $collection_group = CollectionGroupModel::find($id);
        if (empty($collection_group)) {
            return self::error('收藏夹不存在' , '' , 404);
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = CollectionModel::getWithPagerByModuleIdAndUserIdAndCollectionGroupIdAndValueAndRelationTypeAndSize($module->id , user()->id , $collection_group->id , $param['value'] , $param['relation_type'] , $size);
        $res = CollectionHandler::handlePaginator($res);
        Repository::withRelationUsePreloadByModuleAndCollectionSupportCollection($module , $res->data);
        return self::success('' , $res);
    }

    public static function update(Base $context , $id , array $param = []): array
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
        $collection_group = CollectionGroupModel::find($id);
        if (empty($collection_group)) {
            return self::error('收藏夹不存在');
        }
        $user = user();
        if (!empty(CollectionGroupModel::findByModuleIdAndUserIdAndNameExcludeIds($module->id , $user->id , $param['name'] , [$collection_group->id]))) {
            return self::error('名称已经被使用');
        }
        CollectionGroupModel::updateById($collection_group->id , [
            'name' => $param['name']
        ]);
        $res = CollectionGroupModel::find($collection_group->id);
        $res = CollectionGroupHandler::handle($res);
        return self::success('' , $res);
    }

    // 局部更新
    public static function show(Base $context , int $id , array $param = []): array
    {
        $res = CollectionGroupModel::find($id);
        if (empty($res)) {
            return self::error('记录不存在' , '' , 404);
        }
        $res = CollectionGroupHandler::handle($res);
        CollectionGroupHandler::user($res);
        CollectionGroupHandler::thumb($res);
        return self::success('' , $res);
    }
}
