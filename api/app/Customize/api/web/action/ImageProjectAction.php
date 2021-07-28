<?php


namespace App\Customize\api\web\action;

use App\Customize\api\web\handler\CategoryHandler;
use App\Customize\api\web\handler\ImageProjectHandler;
use App\Customize\api\web\handler\RelationTagHandler;
use App\Customize\api\web\handler\ImageSubjectHandler;
use App\Customize\api\web\handler\UserHandler;
use App\Customize\api\web\model\CategoryModel;
use App\Customize\api\web\model\ImageProjectModel;
use App\Customize\api\web\model\ModuleModel;
use App\Customize\api\web\model\PraiseModel;
use App\Customize\api\web\model\RelationTagModel;
use App\Customize\api\web\model\ImageSubjectModel;
use App\Customize\api\web\model\TagModel;
use App\Http\Controllers\api\web\Base;
use Core\Lib\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function api\web\my_config;
use function api\web\my_config_keys;
use function api\web\parse_order;
use function api\web\user;
use function core\object_to_array;

class ImageProjectAction extends Action
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
        $res = ImageProjectModel::getNewestByRelationAndFieldAndFilterAndSize([
            'user' ,
            'tags' ,
            'images' ,
        ] , null , $param , $size);
        $res = ImageProjectHandler::handleAll($res);

        foreach ($res as $v)
        {
            // 附加：用户
            ImageProjectHandler::user($v);
            // 附加：标签
            ImageProjectHandler::tags($v);
            // 附加：是否点赞
            ImageProjectHandler::isPraised($v);
            // 附加：图片数
            ImageProjectHandler::imageCount($v);

            unset($v->images);
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
        $type_range = my_config_keys('business.type_for_image_project');
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
            'type'      => ['required' , Rule::in($type_range)] ,
        ]);

        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = ImageProjectModel::getHotByRelationAndFieldAndFilterAndSize([
            'user' ,
            'tags' ,
            'images' ,
        ] , null , $param, $size);
        $res = ImageProjectHandler::handleAll($res);
        foreach ($res as $v)
        {
            // 附加：用户
            ImageProjectHandler::user($v);
            // 附加：标签
            ImageProjectHandler::tags($v);
            // 附加：是否点赞
            ImageProjectHandler::isPraised($v);
            // 附加：图片数
            ImageProjectHandler::imageCount($v);

            unset($v->images);
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
            'type'      => ['required' , Rule::in($type_range)] ,
            'tag_id'    => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $tag = TagModel::find($param['tag_id']);
        if (empty($tag)) {
            return self::error('标签不存在' , '' , 404);
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = ImageProjectModel::getByRelationAndFieldAndTagIdAndFilterAndSize([
            'user' ,
            'tags' ,
            'images' ,
        ] , null , $tag->id , $param , $size);
        $res = ImageProjectHandler::handleAll($res);
        foreach ($res as $v)
        {
            // 附加：用户
            ImageProjectHandler::user($v);
            // 附加：标签
            ImageProjectHandler::tags($v);
            // 附加：是否点赞
            ImageProjectHandler::isPraised($v);
            // 附加：图片数
            ImageProjectHandler::imageCount($v);

            unset($v->images);
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
        $image_project = ImageProjectModel::find($id);
        if (empty($image_project)) {
            return self::error('图片专题不存在' , '' , 404);
        }
        $image_project = ImageProjectHandler::handle($image_project);
        // 附加：用户
        ImageProjectHandler::user($image_project);
        // 附加：是否关注自身
        UserHandler::focused($image_project->user);
        // 附加：图片
        ImageProjectHandler::images($image_project);
        // 附加：标签
        ImageProjectHandler::tags($image_project);
        // 附加：图片主体
        ImageProjectHandler::imageSubject($image_project);
        // 附加：分类
        ImageProjectHandler::category($image_project);
        // 附加：是否收藏
        ImageProjectHandler::isCollected($image_project);
        // 附加：是否点赞
        ImageProjectHandler::isPraised($image_project);
        return self::success('' , $image_project);
    }


    public static function hotWithPager(Base $context , array $param = [])
    {
        $type_range = my_config_keys('business.type_for_image_project');
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
            'type'      => ['required' , Rule::in($type_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = ImageProjectModel::getHotWithPagerByRelationAndFilterAndSize([
            'user' ,
            'tags' ,
            'images' ,
        ] , $param , $size);
        $res = ImageProjectHandler::handlePaginator($res);
        foreach ($res->data as $v)
        {
            // 附加：用户
            ImageProjectHandler::user($v);
            // 附加：标签
            ImageProjectHandler::tags($v);
            // 附加：是否点赞
            ImageProjectHandler::isPraised($v);
            // 附加：图片数
            ImageProjectHandler::imageCount($v);

            unset($v->images);
        }
        return self::success('' , $res);
    }

    public static function getWithPagerByTagIds(Base $context , array $param = [])
    {
        $type_range = my_config_keys('business.type_for_image_project');
        $mode_range = my_config('business.mode_for_image_project');
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
            'tag_ids'   => 'required' ,
            'mode'      => ['required' , Rule::in($mode_range)] ,
            'type'      => ['required' , Rule::in($type_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $tag_ids = empty($param['tag_ids']) ? [] : json_decode($param['tag_ids'] , true);
        if (empty($tag_ids)) {
            return self::error('请提供过滤的标签');
        }
        $tags = TagModel::find($tag_ids);
        if (count($tags) !== count($tag_ids)) {
            return self::error('部分或全部标签未找到');
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $relation = [
            'user' ,
            'tags' ,
            'images' ,
        ];
        switch ($param['mode'])
        {
            case 'strict':
                $res = ImageProjectModel::getInStrictByRelationAndTagIdsAndFilterAndSize($relation , $tag_ids , $param , $size);
                break;
            case 'loose':
                $res = ImageProjectModel::getInLooseByRelationAndTagIdsAndFilterAndSize($relation , $tag_ids , $param , $size);
                break;
            default:
                return self::error('不支持的 mode ，当前受支持的 mode 有：' . implode(' , ' , $mode_range));
        }
        $res = ImageProjectHandler::handlePaginator($res);
        foreach ($res->data as $v)
        {
            // 附加：用户
            ImageProjectHandler::user($v);
            // 附加：标签
            ImageProjectHandler::tags($v);
            // 附加：是否点赞
            ImageProjectHandler::isPraised($v);
            // 附加：图片数
            ImageProjectHandler::imageCount($v);

            unset($v->images);
        }
        return self::success('' , $res);
    }


    public static function hotTags(Base $context , array $param = [])
    {
        $type_range = my_config_keys('business.type_for_image_project');

        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
            'type'      => ['required' , Rule::in($type_range)] ,
        ]);

        if ($validator->fails()) {
            return self::error($validator->errors()->first() , $validator->errors());
        }

        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = RelationTagModel::hotTagsInImageProjectByFilterAndSize($param , $size);
        $res = RelationTagHandler::handleAll($res);

        return self::success('' , $res);
    }

    public static function hotTagsWithPager(Base $context , array $param = [])
    {
        $type_range = my_config_keys('business.type_for_image_project');
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
            'type'      => ['required' , Rule::in($type_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在');
        }
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = RelationTagModel::hotTagsWithPagerInImageProjectByValueAndFilterAndSize($param['value'] , $param , $size);
        $res = RelationTagHandler::handlePaginator($res);
        return self::success('' , $res);
    }

    public static function newestWithPager(Base $context , array $param = [])
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
        $res = ImageProjectModel::getNewestWithPagerByRelationAndFieldAndFilterAndSize([
            'user' ,
            'tags' ,
            'images' ,
        ] , null , $param , $size);
        $res = ImageProjectHandler::handlePaginator($res);
        foreach ($res->data as $v)
        {
            // 附加：用户
            ImageProjectHandler::user($v);
            // 附加：标签
            ImageProjectHandler::tags($v);
            // 附加：是否点赞
            ImageProjectHandler::isPraised($v);
            // 附加：图片数
            ImageProjectHandler::imageCount($v);

            unset($v->images);
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
        $categories = CategoryModel::getByModuleIdAndType($module->id , 'image_project');
        $categories = CategoryHandler::handleAll($categories);
        $categories = object_to_array($categories);
        $categories = Category::childrens(0 , $categories , null , false , false);
        return self::success('' , $categories);
    }

    public static function imageSubject(Base $context , array $param = [])
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
        $size = $param['size'] === '' ? my_config('app.limit') : $param['size'];
        $res = ImageSubjectModel::getWithPagerInImageProjectByModuleIdAndValueAndSize($module->id , $param['value'] , $size);
        $res = ImageSubjectHandler::handlePaginator($res);
        return self::success('' , $res);
    }

    public static function index(Base $context , array $param = [])
    {
        $type_range = my_config_keys('business.type_for_image_project');
        $mode_range = my_config('business.mode_for_image_project');
        $validator = Validator::make($param , [
            'module_id' => 'required|integer' ,
            'mode'      => ['required' , Rule::in($mode_range)] ,
            'type'      => ['required' , Rule::in($type_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $module = ModuleModel::find($param['module_id']);
        if (empty($module)) {
            return self::error('模块不存在' , '' , 404);
        }
        $param['category_ids']   = $param['category_ids'] === '' ? [] : json_decode($param['category_ids'] , true);
        $param['image_subject_ids']    = $param['image_subject_ids'] === '' ? [] : json_decode($param['image_subject_ids'] , true);
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
        $relation = [
            'user' ,
            'tags' ,
            'images' ,
        ];
        switch ($param['mode'])
        {
            case 'strict':
                $res = ImageProjectModel::getWithPagerInStrictByRelationAndFilterAndOrderAndSize($relation , $param , $order , $size);
                break;
            case 'loose':
                $res = ImageProjectModel::getWithPagerInLooseByRElationAndFilterAndOrderAndSize($relation , $param , $order , $size);
                break;
            default:
                return self::error('不支持的搜索模式，当前支持的模式有：' . implode(' , ' , $mode_range));
        }
        $res = ImageProjectHandler::handlePaginator($res);

        foreach ($res->data as $v)
        {
            // 附加：用户
            ImageProjectHandler::user($v);
            // 附加：标签
            ImageProjectHandler::tags($v);
            // 附加：是否点赞
            ImageProjectHandler::isPraised($v);
            // 附加：图片数
            ImageProjectHandler::imageCount($v);

            unset($v->images);
        }
        return self::success('' , $res);
    }

    public static function incrementViewCount(Base $context , int $image_project_id , array $param = [])
    {
        $image_project = ImageProjectModel::find($image_project_id);
        if (empty($image_project)) {
            return self::error('图片专题不存在');
        }
        ImageProjectModel::countHandle($image_project->id , 'view_count' , 'increment' , 1);
        return self::success('操作成功');
    }

    // 推荐数据
    public static function recommend(Base $context , int $id , array $param = [])
    {
        $type_range = my_config_keys('business.type_for_image_project');

        $validator = Validator::make($param , [
            'type'      => ['required' , Rule::in($type_range)] ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->errors()->first());
        }
        $image_project = ImageProjectModel::find($id);
        if (empty($image_project)) {
            return self::error('图片专题未找到' , null , 404);
        }
        $param['module_id']     = $image_project->module_id ?? '';
        $param['category_id']   = $image_project->category_id ?? '';
        $param['image_subject_id']    = $image_project->image_subject_id ?? '';
        $size = $param['size'] ? $param['size'] : my_config('app.limit');
        $res = ImageProjectModel::recommendExcludeSelfByFilterAndSize($image_project->id , $param , $size);
        $res = ImageProjectHandler::handleAll($res);
        return self::success('' , $res);
    }
}
