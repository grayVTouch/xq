<?php


namespace App\Customize\api\admin\repository;


use App\Customize\api\admin\handler\ImageProjectHandler;
use App\Customize\api\admin\handler\VideoHandler;
use App\Customize\api\admin\handler\VideoProjectHandler;
use App\Customize\api\admin\model\ImageProjectModel;
use App\Customize\api\admin\model\VideoModel;
use App\Customize\api\admin\model\VideoProjectModel;
use Exception;
use function api\admin\my_config;

class StorageRepository
{
    /**
     * 图片/图片专题：获取相对目录名称
     *
     * @param int $image_project_id
     * @return string
     * @throws Exception
     */
    public static function getRelativeDirectoryByImageProjectId(int $image_project_id): string
    {
        $image_project = ImageProjectModel::find($image_project_id);
        if (empty($image_project)) {
            throw new Exception("图片专题记录不存在【{$image_project_id}】");
        }
        $dir_prefix = '';
        if ($image_project->type === 'pro') {
            $dir_prefix = my_config('app.dir')['image_project'];
        } else {
            $dir_prefix = my_config('app.dir')['image'] . '/' . date('Ymd' , strtotime($image_project->created_at));
        }
        // 附加：模块
        ImageProjectHandler::module($image_project);
        // 保存目录
        $pathname = !empty($image_project->module) ? $image_project->module->name  . '/' : '';
        $pathname .= $dir_prefix;
        $pathname .= $image_project->type === 'pro' ? '/' . $image_project->name : '';

        return $pathname;
    }

    /**
     * 视频：获取相对目录名称
     *
     * @param int $video_id
     * @return string
     * @throws Exception
     */
    public static function getRelativeDirectoryByVideoId(int $video_id): string
    {
        $video = VideoModel::find($video_id);
        if (empty($video)) {
            throw new Exception("视频记录不存在【{$video_id}】");
        }
        $dir_prefix = '';
        $dirname    = '';
        if ($video->type === 'pro') {
            VideoHandler::videoProject($video);
            if (empty($video->video_project)) {
                throw new Exception("视频属于视频专题，但是对应的视频专题【{$video->video_project_id}】记录未找到");
            }
            $dir_prefix = my_config('app.dir')['video_project'];
            $dirname = $video->video_project->name;
        } else {
            $dir_prefix = my_config('app.dir')['video'] . '/' . date('Ymd' , strtotime($video->created_at));
            $dirname    = $video->name;
        }
        VideoHandler::module($video);
        $pathname = !empty($video->module) ? $video->module->name . '/' : '';
        $pathname .= $dir_prefix . '/' . $dirname;
        return $pathname;
    }

    /**
     * 视频专题：获取相对目录名称
     *
     * @param int $video_project_id
     * @return string
     * @throws Exception
     */
    public static function getRelativeDirectoryByVideoProjectId(int $video_project_id): string
    {
        $video_project = VideoProjectModel::find($video_project_id);
        if (empty($video_project)) {
            throw new Exception("视频专题记录不存在【{$video_project_id}】");
        }
        VideoProjectHandler::module($video_project);
        $dir_prefix = my_config('app.dir')['video_project'];
        $pathname = !empty($video_project->module) ? $video_project->module->name . '/' : '';
        $pathname .= $dir_prefix . '/' . $video_project->name;
        return $pathname;
    }

}
