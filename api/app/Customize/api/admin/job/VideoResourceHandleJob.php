<?php

namespace App\Customize\api\admin\job;

use App\Customize\api\admin\facade\AliyunOss;
use App\Customize\api\admin\handler\VideoHandler;
use App\Customize\api\admin\job\middleware\BootMiddleware;
use App\Customize\api\admin\model\ImageModel;
use App\Customize\api\admin\model\SystemSettingsModel;
use App\Customize\api\admin\model\VideoModel;
use App\Customize\api\admin\model\ResourceModel;
use App\Customize\api\admin\model\VideoProjectModel;
use App\Customize\api\admin\model\VideoSrcModel;
use App\Customize\api\admin\model\VideoSubtitleModel;
use App\Customize\api\admin\repository\StorageRepository;
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

class VideoResourceHandleJob extends FileBaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $videoId = 0;

    /**
     * @var string
     */
    private $relativeDir = '';

    /**
     * @var string
     */
    private $saveDir = '';

    /**
     * @var VideoModel
     */
    private $video;

    /**
     * @var SystemSettingsModel
     */
    private $settings;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $video_id)
    {
        $this->videoId = $video_id;
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
        // 仅在本地存储模式下才执行移植任务
        $video = VideoModel::find($this->videoId);
        if (empty($video)) {
            throw new Exception('视频不存在【' . $this->videoId . '】');
        }
        if ($video->file_process_status !== 0) {
            // 当前文件处理状态无需处理
            return ;
        }
        VideoModel::updateById($this->videoId , [
            // 处理中
            'file_process_status' => 1 ,
        ]);
        $video = VideoHandler::handle($video);
        // 附加：视频
        VideoHandler::videos($video);
        // 附加：字幕
        VideoHandler::videoSubtitles($video);

        $relative_dir   = StorageRepository::getRelativeDirectoryByVideoId($video->id);
        $save_dir       = FileRepository::generateRealPathByWithoutPrefixRelativePath($relative_dir);
        if (!File::exists($save_dir)) {
            File::mkdir($save_dir , 0777 , true);
        }
        if ($video->type === 'pro') {
            // 记录保存的目录
            ResourceRepository::create('' , $save_dir , 'local' , 1 , 0);
            VideoProjectModel::updateById($video->video_project_id , [
                'directory' => $save_dir ,
            ]);
        }

        $this->video = $video;
        $this->saveDir = $save_dir;
        $this->relativeDir = $relative_dir;
        $this->settings = SystemSettingsModel::first();

        if ($video->disk === 'local') {
            $this->localHandle();
        } else if ($video->disk === 'aliyun') {
            $this->aliyunCloudHandle();
        } else {
            // todo 其他预留
        }

        VideoModel::updateById($this->videoId , [
            // 处理已完成
            'file_process_status' => 2 ,
        ]);
    }

    public function aliyunCloudHandle()
    {
        $video_name = $this->getVideoName($this->video->type , $this->video->name , $this->video->index);


        /**
         * ********************************************
         * 第一帧
         * ********************************************
         */
        $o_video_first_frame_file = AliyunOss::getPathname($this->video->thumb_for_program);
        $n_video_first_frame_file = $this->relativeDir . '/' . $this->generateMediaSuffix($this->video->type , $video_name . '【第一帧】' , 'jpeg');
        if ($o_video_first_frame_file !== $n_video_first_frame_file) {
            $resource = ResourceModel::findByUrlOrPath($this->video->thumb_for_program);
            if (empty($resource)) {
                throw new Exception("资源记录未找到：{$this->video->thumb_for_program}");
            }
            if ($resource->disk === 'aliyun') {
                $upload_res = AliyunOss::copy($resource->aliyun_bucket , $o_video_first_frame_file , $this->settings->aliyun_bucket , $n_video_first_frame_file);
                if ($upload_res['data'] > 0) {
                    throw new Exception($upload_res['message']);
                }
                VideoModel::updateById($this->video->id , [
                    'thumb_for_program' => $upload_res['data']
                ]);
                // 删除源文件
                ResourceRepository::delete($this->video->thumb_for_program);
                ResourceRepository::createAliyun($upload_res['data'] , $this->settings->aliyun_bucket , 1 , 0);
            }
        }

        /**
         * ********************************************
         * 预览视频
         * ********************************************
         */
        $o_video_simple_preview_file      = AliyunOss::getPathname($this->video->simple_preview);;
        $n_video_simple_preview_file      = $this->relativeDir . '/' . $this->generateMediaSuffix($this->video->type , $video_name . '【预览】' , 'mp4');
        if ($o_video_simple_preview_file !== $n_video_simple_preview_file) {
            $resource = ResourceModel::findByUrlOrPath($this->video->simple_preview);
            if (empty($resource)) {
                throw new Exception("资源记录未找到：{$this->video->simple_preview}");
            }
            if ($resource->disk === 'aliyun') {
                $upload_res = AliyunOss::copy($resource->aliyun_bucket , $o_video_simple_preview_file , $this->settings->aliyun_bucket , $n_video_simple_preview_file);
                if ($upload_res['data'] > 0) {
                    throw new Exception($upload_res['message']);
                }
                VideoModel::updateById($this->video->id , [
                    'simple_preview' => $upload_res['data']
                ]);
                // 删除源文件
                ResourceRepository::delete($this->video->simple_preview);
                ResourceRepository::createAliyun($upload_res['data'] , $this->settings->aliyun_bucket , 1 , 0);
            }
        }

        /**
         * ********************************************
         * 预览图
         * ********************************************
         */
        $o_preview_file = AliyunOss::getPathname($this->video->preview);;
        $n_preview_file = $this->relativeDir . '/' . $this->generateMediaSuffix($this->video->type , $video_name . '【预览】' ,'jpeg');
        if ($o_preview_file !== $n_preview_file) {
            $resource = ResourceModel::findByUrlOrPath($this->video->preview);
            if (empty($resource)) {
                throw new Exception("资源记录未找到：{$this->video->preview}");
            }
            if ($resource->disk === 'aliyun') {
                $upload_res = AliyunOss::copy($resource->aliyun_bucket , $o_preview_file , $this->settings->aliyun_bucket , $n_preview_file);
                if ($upload_res['data'] > 0) {
                    throw new Exception($upload_res['message']);
                }
                VideoModel::updateById($this->video->id , [
                    'preview' => $upload_res['data']
                ]);
                // 删除源文件
                ResourceRepository::delete($this->video->preview);
                ResourceRepository::createAliyun($upload_res['data'] , $this->settings->aliyun_bucket , 1 , 0);
            }
        }

        // 视频文件
        foreach ($this->video->videos as $v)
        {
            try {
                DB::beginTransaction();
                $resource = ResourceModel::findByUrlOrPath($v->src);
                if (empty($resource)) {
                    continue ;
                }
                if ($resource->disk !== 'aliyun') {
                    // 跳过非本地存储的资源
                    continue ;
                }
                $extension      = get_extension($v->src);
                $filename       = $this->generateVideoMediaSuffix($this->video->type , $v->definition , $this->video->index , $this->video->name , $extension);
                $source_file    = AliyunOss::getPathname($resource->url);
                $target_file    = $this->relativeDir . '/' . $filename;
                if ($source_file == $target_file) {
                    continue ;
                }
                $upload_res = AliyunOss::copy($resource->aliyun_bucket, $source_file, $this->settings->aliyun_bucket, $target_file);
                if ($upload_res['data'] > 0) {
                    throw new Exception($upload_res['message']);
                }
                VideoSrcModel::updateById($v->id , [
                    'src' => $upload_res['data']
                ]);
                // 删除源文件
                ResourceRepository::delete($v->src);
                ResourceRepository::createAliyun($upload_res['data'] , $this->settings->aliyun_bucket , 1 , 0);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
        // 字幕文件
        $video_subtitle_config = my_config('app.video_subtitle');
        foreach ($this->video->video_subtitles as $v)
        {
            try {
                DB::beginTransaction();
                $resource = ResourceModel::findByUrlOrPath($v->src);
                if (empty($resource)) {
                    DB::rollBack();
                    continue ;
                }
                if (!in_array($resource->disk ,['local' , 'aliyun'])) {
                    DB::rollBack();
                    continue ;
                }
                $filename = $this->generateMediaSuffix($this->video->type , "{$video_name}【{$v->name}】" , $video_subtitle_config['extension']);
                $aliyun_file = $this->relativeDir . '/' . $filename;
                if ($resource->disk === 'local') {
                    $extension      = get_extension($v->src);
                    $source_file    = $resource->path;
                    $target_file    = $this->generateRealPath($this->saveDir , $filename);

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
                            ResourceRepository::create('' , $source_file , 'local' , 0 , 1);
                            ResourceRepository::create('' , $target_file , 'local' , 0 , 0);
                        } else {
                            $target_file = $source_file;
                        }
                        $upload_res = AliyunOss::upload($this->settings->aliyun_bucket , $aliyun_file , $target_file);
                        if ($upload_res['code'] > 0) {
                            throw new Exception($upload_res['message']);
                        }
                        VideoSubtitleModel::updateById($v->id , [
                            'src' => $upload_res['data']
                        ]);
                        // 删除源文件
                        ResourceRepository::delete($target_file);
                        ResourceRepository::createAliyun($upload_res['data'] , $this->settings->aliyun_bucket , 1 , 0);
                    }
                } else {
                    // 阿里云
                    $o_file = AliyunOss::getPathname($resource->url);
                    $n_file = $aliyun_file;
                    if (!AliyunOss::isRepeat($resource->aliyun_bucket , $o_file , $this->settings->aliyun_bucket , $n_file)) {
                        $copy_res = AliyunOss::copy($resource->aliyun_bucket , $o_file , $this->settings->aliyun_bucket , $n_file);
                        if ($copy_res['code'] > 0) {
                            throw new Exception($copy_res['data']);
                        }
                        VideoSubtitleModel::updateById($v->id , [
                            'src' => $copy_res['data']
                        ]);
                        ResourceRepository::delete($resource->url);
                        ResourceRepository::createAliyun($copy_res['data'] , $this->settings->aliyun_bucket , 1 , 0);
                    }
                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
    }

    public function localHandle()
    {
        $video_name = $this->getVideoName($this->video->type , $this->video->name , $this->video->index);
        // 第一帧图片 + 预览图片 + 预览视频
        $video_first_frame_file         = $this->generateRealPath($this->saveDir , $this->generateMediaSuffix($this->video->type , $video_name . '【第一帧】' , 'jpeg'));
        $video_first_frame_url          = FileRepository::generateUrlByRealPath($video_first_frame_file);
        $video_simple_preview_file      = $this->generateRealPath($this->saveDir , $this->generateMediaSuffix($this->video->type , $video_name . '【预览】' , 'mp4'));
        $video_simple_preview_url       = FileRepository::generateUrlByRealPath($video_simple_preview_file);
        $preview_file                   = $this->generateRealPath($this->saveDir , $this->generateMediaSuffix($this->video->type , $video_name . '【预览】' ,'jpeg'));
        $preview_url                    = FileRepository::generateUrlByRealPath($preview_file);
        if (!File::exists($video_first_frame_file)) {
            // 移动文件
            $resource = ResourceModel::findByUrlOrPath($this->video->thumb_for_program);
            if (empty($resource)) {
                throw new Exception("资源记录未找到：{$this->video->thumb_for_program}");
            }
            if ($resource->disk === 'local') {
                File::move($resource->path , $video_first_frame_file);
                VideoModel::updateById($this->video->id , [
                    'thumb_for_program' => $video_first_frame_url
                ]);
                // 删除源文件
                ResourceRepository::delete($this->video->thumb_for_program);
                ResourceRepository::create($video_first_frame_url , $video_first_frame_file , 'local' , 1 , 0);
            }
        }
        if (!File::exists($video_simple_preview_file)) {
            // 移动文件
            $resource = ResourceModel::findByUrlOrPath($this->video->simple_preview);
            if (empty($resource)) {
                throw new Exception("资源记录未找到：{$this->video->simple_preview}");
            }
            if ($resource->disk === 'local') {
                File::move($resource->path , $video_simple_preview_file);
                VideoModel::updateById($this->video->id , [
                    'simple_preview' => $video_simple_preview_url
                ]);
                // 删除源文件
                ResourceRepository::delete($this->video->simple_preview);
                ResourceRepository::create($video_simple_preview_url , $video_simple_preview_file , 'local' , 1 , 0);
            }
        }
        if (!File::exists($preview_file)) {
            // 移动文件
            $resource = ResourceModel::findByUrlOrPath($this->video->preview);
            if (empty($resource)) {
                throw new Exception("资源记录未找到：{$this->video->preview}");
            }
            if ($resource->disk === 'local') {
                File::move($resource->path , $preview_file);
                VideoModel::updateById($this->video->id , [
                    'preview' => $preview_url
                ]);
                // 删除源文件
                ResourceRepository::delete($this->video->preview);
                ResourceRepository::create($preview_url , $preview_file , 'local' , 1 , 0);
            }
        }
        // 视频文件
        foreach ($this->video->videos as $v)
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
                $filename       = $this->generateVideoMediaSuffix($this->video->type , $v->definition , $this->video->index , $this->video->name , $extension);
                $source_file    = $resource->path;
                $target_file    = $this->generateRealPath($this->saveDir , $filename);
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
        foreach ($this->video->video_subtitles as $v)
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
                $filename       =  $this->generateMediaSuffix($this->video->type , "{$video_name}【{$v->name}】" , 'vtt');
                $source_file    = $resource->path;
                $target_file    = $this->generateRealPath($this->saveDir , $filename);
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

    private function getVideoName($type , $name , $index)
    {
        if ($type === 'pro') {
            // [sprintf 函数可访问右侧链接](https://www.runoob.com/php/func-string-sprintf.html)
            return empty($name) ? sprintf("%'04s" , $index) : $name;
        }
        return $name;
    }

    private function generateVideoMediaSuffix(string $type , string $definition , ?int $index , string $name , string $extension): string
    {
        if ($type === 'misc') {
            return $name . '【' . $definition . '】' . '【' . random(8 , 'letter' , true) . '】' . '.' . $extension;
        }
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
    private function generateMediaSuffix(string $type , string $name , string $extension): string
    {
        return $type === 'pro' ? $name . '.' . $extension : $name . '【' . random(8 , 'letter' , true) . '】' . '.' . $extension;
    }

    public function failed(Exception $e)
    {
        VideoModel::updateById($this->videoId , [
            // 处理失败
            'file_process_status' => -1 ,
            'file_process_message' => $e->getMessage() ,
            'file_process_data' => $e->getTraceAsString() ,
        ]);
    }
}
