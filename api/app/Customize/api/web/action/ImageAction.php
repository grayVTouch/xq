<?php


namespace App\Customize\api\web\action;

use App\Customize\api\web\handler\CategoryHandler;
use App\Customize\api\web\handler\ImageHandler;
use App\Customize\api\web\handler\RelationTagHandler;
use App\Customize\api\web\handler\ImageSubjectHandler;
use App\Customize\api\web\handler\UserHandler;
use App\Customize\api\web\model\CategoryModel;
use App\Customize\api\web\model\ImageModel;
use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\PraiseModel;
use App\Customize\api\web\model\RelationTagModel;
use App\Customize\api\web\model\ImageSubjectModel;
use App\Customize\api\web\model\TagModel;
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
use function core\object_to_array;

class ImageAction extends Action
{
    /**
     * @param Base $context
     * @param array $param
     * @return array
     * @throws \Exception
     */
    public static function newest(Base $context , array $param = [])
    {
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $param['type'] = 'misc';
        $res = ImageModel::getNewestByFilterAndSize($param , $size);
        $res = ImageHandler::handleAll($res);
        foreach ($res as $v)
        {
            // 附加：是否点赞
            ImageHandler::isPraised($v);
        }
        return self::success('' , $res);
    }

    /**
     * 热门
     *
     * @param Base $context
     * @param array $param
     * @return array
     * @throws \Exception
     */
    public static function hot(Base $context , array $param = [])
    {
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
        ]);

        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = ImageModel::getHotByFilterAndSize($param , $size);
        $res = ImageHandler::handleAll($res);
        foreach ($res as $v)
        {
            // 附加：是否点赞
            ImageHandler::isPraised($v);
        }
        return self::success('' , $res);
    }

    /**
     * @param Base $context
     * @param $tag_id
     * @param array $param
     * @return array
     * @throws \Exception
     */
    public static function getByTagId(Base $context , array $param = []): array
    {
        $type_range = my_config_keys('business.type_for_image_project');
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
            'tag_id'    => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $tag = TagModel::find($param['tag_id']);
        if (empty($tag)) {
            return self::error('标签不存在' , '' , 404);
        }
        $param['type'] = 'misc';
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = ImageModel::getByTagIdAndFilterAndSize($tag->id , $param , $size);
        $res = ImageHandler::handleAll($res);
        foreach ($res as $v)
        {
            // 附加：是否点赞
            ImageHandler::isPraised($v);
        }
        return self::success('' , $res);
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
        $image = ImageModel::find($id);
        if (empty($image)) {
            return self::error('图片不存在' , '' , 404);
        }
        $image = ImageHandler::handle($image);
        // 附加：用户
        ImageHandler::user($image);
        // 附加：是否关注自身
        UserHandler::focused($image->user);
        // 附加：标签
        ImageHandler::tags($image);
        // 附加：是否收藏
        ImageHandler::isCollected($image);
        // 附加：是否点赞
        ImageHandler::isPraised($image);
        return self::success('' , $image);
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
        $param['type'] = 'misc';
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = RelationTagModel::hotTagsInImageProjectByFilterAndSize($param , $size);
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
        $res = RelationTagModel::hotTagsWithPagerInImageByValueAndFilterAndSize($param['value'] , $param , $size);
        $res = RelationTagHandler::handlePaginator($res);
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
        $categories = CategoryModel::getByModuleIdAndType($module->id , 'image');
        $categories = CategoryHandler::handleAll($categories);
        $categories = object_to_array($categories);
        $categories = Category::childrens(0 , $categories , null , false , false);
        return self::success('' , $categories);
    }

    public static function index(Base $context , array $param = [])
    {
        $mode_range = my_config('business.mode_for_image_project');
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
        $param['type'] = 'misc';
        $param['category_ids']   = $param['category_ids'] === '' ? [] : json_decode($param['category_ids'] , true);
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
                $res = ImageModel::getWithPagerInStrictByFilterAndOrderAndSize($param , $order , $size);
                break;
            case 'loose':
                $res = ImageModel::getWithPagerInLooseByFilterAndOrderAndSize($param , $order , $size);
                break;
            default:
                return self::error('不支持的搜索模式，当前支持的模式有：' . implode(' , ' , $mode_range));
        }
        $res = ImageHandler::handlePaginator($res);
        foreach ($res->data as $v)
        {
            // 附加：是否点赞
            ImageHandler::isPraised($v);
        }
        return self::success('' , $res);
    }

    public static function incrementViewCount(Base $context , int $id , array $param = [])
    {
        $res = ImageModel::find($id);
        if (empty($res)) {
            return self::error('图片记录不存在');
        }
        ImageModel::incrementByIdAndColumnAndStep($res->id , 'view_count' , 1);
        return self::success('操作成功');
    }

    // 推荐数据
    public static function recommend(Base $context , int $id , array $param = [])
    {
        $validator = Validator::make($param , [

        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $image = ImageModel::find($id);
        if (empty($image)) {
            return self::error('图片记录未找到' , null , 404);
        }
        $param['module_id']     = $image->module_id ?? '';
        $param['category_id']   = $image->category_id ?? '';
        $size = $param['size'] ? $param['size'] : my_config('app.limit');
        $res = ImageModel::recommendExcludeSelfByFilterAndSize($image->id , $param , $size);
        $res = ImageHandler::handleAll($res);
        foreach ($res as $v)
        {
            // 附加：是否点赞
            ImageHandler::isPraised($v);
        }
        return self::success('' , $res);
    }

    //
    public static function praiseHandle(Base $context , int $id , array $param = [])
    {
        $action_range           = my_config_keys('business.bool_for_int');
        $validator = Validator::make($param , [
            'module_id'             => 'required|integer' ,
            'action'                => ['required' , Rule::in($action_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        $image = ImageModel::find($id);
        if (empty($image)) {
            return self::error('图片记录不存在' , '' , 404);
        }
        $user = user();
        try {
            DB::beginTransaction();
            if ($param['action'] == 1) {
                // 点赞
                PraiseModel::insertOrIgnore([
                    'module_id' => $module->id ,
                    'user_id' => $user->id ,
                    'relation_type' => 'image' ,
                    'relation_id' => $image->id ,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                ImageModel::incrementByIdAndColumnAndStep($image->id , 'praise_count' , 1);
            } else {
                // 取消收藏
                PraiseModel::delByModuleIdAndUserIdAndRelationTypeAndRelationId($module->id , $user->id , 'image' , $image->id);
                ImageModel::decrementByIdAndColumnAndStep($image->id , 'praise_count' , 1);
            }
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
