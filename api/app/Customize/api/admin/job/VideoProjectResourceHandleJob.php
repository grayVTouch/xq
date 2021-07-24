<?php

namespace App\Customize\api\admin\job;

use App\Customize\api\admin\handler\VideoHandler;
use App\Customize\api\admin\handler\VideoProjectHandler;
use App\Customize\api\admin\job\middleware\BootMiddleware;
use App\Customize\api\admin\model\ImageModel;
use App\Customize\api\admin\model\VideoModel;
use App\Customize\api\admin\model\ResourceModel;
use App\Customize\api\admin\model\VideoProjectModel;
use App\Customize\api\admin\model\VideoSrcModel;
use App\Customize\api\admin\model\VideoSubtitleModel;
use App\Customize\api\admin\repository\FileRepository;
use App\Customize\api\admin\repository\ResourceRepository;
use Core\Lib\File;
use Core\Wrapper\FFmpeg;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use function api\admin\my_config;
use function core\get_extension;
use function core\random;

class VideoProjectResourceHandleJob extends FileBaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $videoProjectId = 0;

    private $originalName = '';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $video_project_id , string $original_name = '')
    {
        $this->videoProjectId   = $video_project_id;
        $this->originalName     = $original_name;
    }

    public function middleware()
    {
        return [
            new BootMiddleware() ,
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $disk = my_config('app.disk');
        if ($disk === 'local') {
            // 仅在本地存储模式下才执行移植任务
            $video_project = VideoProjectModel::find($this->videoProjectId);
            if (empty($video_project)) {
                throw new Exception('视频专题不存在【' . $this->videoProjectId . '】');
            }
            if ($video_project->file_process_status !== 0) {
                // 当前文件处理状态无需处理
                return ;
            }
            if ($video_project->name === $this->originalName) {
                // 名称一样无需处理
                VideoProjectModel::updateById($video_project->id , [
                    // 处理已完成
                    'file_process_status' => 2 ,
                ]);
                return ;
            }
            VideoProjectHandler::module($video_project);
            if (empty($video_project->module)) {
                throw new Exception("视频专题所属模块不存在【{$video_project->module_id}】");
            }
            VideoProjectModel::updateById($video_project->id , [
                // 处理中
                'file_process_status' => 1 ,
            ]);
            $dir_prefix = my_config('app.dir')['video_project'];
            $target_save_dir = FileRepository::generateRealPathByWithoutPrefixRelativePath($video_project->module->name . '/' . $dir_prefix . '/' . $video_project->name);
            if (empty($this->originalName)) {
                // 添加动作 - 仅创建目录即可
                if (!File::exists($target_save_dir)) {
                    File::mkdir($target_save_dir , 0777 , true);
                }
                ResourceRepository::create('' , $target_save_dir , 'local' , 1 , 0);
                VideoProjectModel::updateById($video_project->id , [
                    'directory' => $target_save_dir ,
                    'file_process_status' => 2 ,
                ]);
                return ;
            }
            // 保存目录
            $origin_save_dir = FileRepository::generateRealPathByWithoutPrefixRelativePath($video_project->module->name . '/' . $dir_prefix . '/' . $this->originalName);
            if (!File::isDir($origin_save_dir)) {
                // 源目录不存在则创建
                File::mkdir($origin_save_dir , 0777 , true);
            }
            if (File::exists($target_save_dir)) {
                // 目录已经存在则删除
                File::delete($target_save_dir);
            }
            File::mkdir($target_save_dir , 0777 , true);
            ResourceRepository::create('' , $target_save_dir , 'local' , 1 , 0);
            VideoProjectModel::updateById($video_project->id , [
                'directory' => $target_save_dir ,
            ]);
            VideoProjectHandler::videos($video_project);
            $save_dir = $target_save_dir;

            // 修改文件内的相关数据
            // 处理文件名称
            $get_video_name = function($name , $index){
                // [sprintf 函数可访问右侧链接](https://www.runoob.com/php/func-string-sprintf.html)
                return empty($name) ? sprintf("%'04s" , $index) : $name;
            };
            foreach ($video_project->videos as $video)
            {
                // 附加：视频源
                VideoHandler::videos($video);
                // 附加：字幕源
                VideoHandler::videoSubtitles($video);

                $video_name = $get_video_name($video->name , $video->index);
                // 第一帧图片 + 预览图片 + 预览视频
                $video_first_frame_file         = $this->generateRealPath($save_dir , $this->generateMediaSuffix($video_name . '【第一帧】' , 'jpeg'));
                $video_first_frame_url          = FileRepository::generateUrlByRealPath($video_first_frame_file);
                $video_simple_preview_file      = $this->generateRealPath($save_dir , $this->generateMediaSuffix($video_name . '【预览】' , 'mp4'));
                $video_simple_preview_url       = FileRepository::generateUrlByRealPath($video_simple_preview_file);
                $preview_file                   = $this->generateRealPath($save_dir , $this->generateMediaSuffix($video_name . '【预览】' ,'jpeg'));
                $preview_url                    = FileRepository::generateUrlByRealPath($preview_file);
                if (!File::exists($video_first_frame_file)) {
                    // 移动文件
                    $resource = ResourceModel::findByUrlOrPath($video->thumb_for_program);
                    if (empty($resource)) {
                        throw new Exception("资源记录未找到：{$video->thumb_for_program}");
                    }
                    File::move($resource->path , $video_first_frame_file);
                    VideoModel::updateById($video->id , [
                        'thumb_for_program' => $video_first_frame_url
                    ]);
                    // 删除源文件
                    ResourceRepository::delete($video->thumb_for_program);
                    ResourceRepository::create($video_first_frame_url , $video_first_frame_file , 'local' , 1 , 0);
                }
                if (!File::exists($video_simple_preview_file)) {
                    // 移动文件
                    $resource = ResourceModel::findByUrlOrPath($video->simple_preview);
                    if (empty($resource)) {
                        throw new Exception("资源记录未找到：{$video->simple_preview}");
                    }
                    File::move($resource->path , $video_simple_preview_file);
                    VideoModel::updateById($video->id , [
                        'simple_preview' => $video_simple_preview_url
                    ]);
                    // 删除源文件
                    ResourceRepository::delete($video->simple_preview);
                    ResourceRepository::create($video_simple_preview_url , $video_simple_preview_file , 'local' , 1 , 0);
                }
                if (!File::exists($preview_file)) {
                    // 移动文件
                    $resource = ResourceModel::findByUrlOrPath($video->preview);
                    if (empty($resource)) {
                        throw new Exception("资源记录未找到：{$video->preview}");
                    }
                    File::move($resource->path , $preview_file);
                    VideoModel::updateById($video->id , [
                        'preview' => $preview_url
                    ]);
                    // 删除源文件
                    ResourceRepository::delete($video->preview);
                    ResourceRepository::create($preview_url , $preview_file , 'local' , 1 , 0);
                }
                // 视频文件
                foreach ($video->videos as $v)
                {
                    try {
                        DB::beginTransaction();
                        $resource = ResourceModel::findByUrlOrPath($v->src);
                        if (empty($resource)) {
                            DB::rollBack();
                            continue ;
                        }
                        if ($resource->disk !== 'local') {
                            // 跳过非本地存储的资源
                            DB::rollBack();
                            continue ;
                        }
                        $extension      = get_extension($v->src);
                        $filename       = $this->generateVideoMediaSuffix($v->definition , $video->index , $video->name , $extension);
                        $source_file    = $resource->path;
                        $target_file    = $this->generateRealPath($save_dir , $filename);
                        if ($source_file !== $target_file) {
                            if (File::exists($target_file)) {
                                // 文件已经存在，删除
                                File::dFile($target_file);
                            }
                            $target_url = FileRepository::generateUrlByRealPath($target_file);
                            // 移动文件
                            File::move($source_file , $target_file);
                            VideoSrcModel::updateById($v->id , [
                                'src' => $target_url
                            ]);
                            // 删除源文件
                            ResourceRepository::delete($v->src);
                            ResourceRepository::create($target_url , $target_file , 'local' , 1 , 0);
                        }
                        DB::commit();
                    } catch (Exception $e) {
                        DB::rollBack();
                        throw $e;
                    }
                }
                // 字幕文件
                foreach ($video->video_subtitles as $v)
                {
                    try {
                        DB::beginTransaction();
                        $resource = ResourceModel::findByUrlOrPath($v->src);
                        if (empty($resource)) {
                            DB::rollBack();
                            continue ;
                        }
                        if ($resource->disk !== 'local') {
                            // 跳过非本地存储的资源
                            DB::rollBack();
                            continue ;
                        }
                        $extension      = get_extension($v->src);
                        $filename       =  $this->generateMediaSuffix("{$video_name}【{$v->name}】" , 'vtt');
                        $source_file    = $resource->path;
                        $target_file    = $this->generateRealPath($save_dir , $filename);
                        if ($source_file !== $target_file) {
                            if (File::exists($target_file)) {
                                // 文件已经存在，删除
                                File::dFile($target_file);
                            }
                            if (!in_array($extension , ['vtt'])) {
                                // 非 web vtt 格式 - 转码 并保存到目标位置
                                FFmpeg::create()
                                    ->input($resource->path)
                                    ->quiet()
                                    ->save($target_file);
                            } else {
                                // 移动文件
                                File::move($source_file , $target_file);
                            }
                            $target_url = FileRepository::generateUrlByRealPath($target_file);
                            VideoSubtitleModel::updateById($v->id , [
                                'src' => $target_url
                            ]);
                            // 删除源文件
                            ResourceRepository::delete($v->src);
                            ResourceRepository::create($target_url , $target_file , 'local' , 1 , 0);
                        }
                        DB::commit();
                    } catch (Exception $e) {
                        DB::rollBack();
                        throw $e;
                    }
                }
            }
            // 删除：源目录
            File::delete($origin_save_dir);
        }
        VideoProjectModel::updateById($video_project->id , [
            // 处理已完成
            'file_process_status' => 2 ,
        ]);
    }

    private function generateVideoMediaSuffix(string $definition , ?int $index , string $name , string $extension): string
    {
        if ($index < 10) {
            $index = '000' . $index;
        } else if ($index < 100) {
            $index = '00' . $index;
        } else if ($index < 1000) {
            $index = '0' . $index;
        } else {
            // 其他
        }
        return $name . '【' . $definition . '】 ' . $index . '.' . $extension;
    }

    // 生成媒体的后缀
    private function generateMediaSuffix(string $name , string $extension): string
    {
        return $name . '.' . $extension;
    }


    // 任务执行失败的时候提示的错误信息
    public function failed(Exception $e)
    {
        VideoProjectModel::updateById($this->videoProjectId , [
            // 处理失败
            'file_process_status' => -1 ,
        ]);
    }
}
