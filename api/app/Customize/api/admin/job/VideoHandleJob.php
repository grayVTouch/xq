<?php

namespace App\Customize\api\admin\job;

use App\Customize\api\admin\handler\VideoHandler;
use App\Customize\api\admin\job\middleware\BootMiddleware;
use App\Customize\api\admin\model\DiskModel;
use App\Customize\api\admin\model\ResourceModel;
use App\Customize\api\admin\model\VideoModel;
use App\Customize\api\admin\model\VideoProjectModel;
use App\Customize\api\admin\model\VideoSrcModel;
use App\Customize\api\admin\model\VideoSubtitleModel;
use App\Customize\api\admin\repository\ResourceRepository;
use Core\Lib\File;
use Core\Lib\ImageProcessor;
use Core\Wrapper\FFmpeg;
use Core\Wrapper\FFprobe;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Customize\api\admin\util\FileUtil;
use function api\admin\my_config;
use function core\format_path;
use function core\random;

class VideoHandleJob extends FileBaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 视频源
     *
     * @var int|string
     */
    private $videoId = '';

    /**
     * 临时目录
     *
     * @var string
     */
    private $tempDir = '';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $video_id)
    {
        $this->videoId  = $video_id;
    }

    /**
     * 获取任务应该通过的中间件
     *
     * @return array
     */
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
            $video = VideoModel::find($this->videoId);
            if (empty($video)) {
                throw new Exception('未找到 videoId:' . $this->videoId . ' 对应记录');
            }
            if ($video->video_process_status !== 0) {
                // 如果处理状态不是 0-待处理则不作任何处理
                return ;
            }
            // 重置视频处理状态
            VideoModel::updateById($this->videoId , [
                // 1-处理中
                'video_process_status' => 1 ,
            ]);
            $video = VideoHandler::handle($video);
            // 附加：模块
            VideoHandler::module($video);
            if (empty($video->module)) {
                throw new Exception("视频所属模块不存在【{$video->module_id}】");
            }
            // 附加：视频专题
            VideoHandler::videoProject($video);
            // 附加：视频
            VideoHandler::videos($video);
            // 附加：字幕
            VideoHandler::videoSubtitles($video);

            $dir_prefix = '';
            $dirname    = '';
            if ($video->type === 'pro') {
                if (empty($video->video_project)) {
                    throw new Exception("视频属于视频专题，但是对应的视频专题【{$video->video_project_id}】记录未找到");
                }
                $dir_prefix = my_config('app.dir')['video_project'];
                $dirname = $video->video_project->name;
            } else {
                $dir_prefix = my_config('app.dir')['video'] . '/' . date('Ymd' , strtotime($video->created_at));
                $dirname    = $video->name;
            }
            $save_dir = FileUtil::generateRealPathByWithoutPrefixRelativePath($video->module->name . '/' . $dir_prefix . '/' . $dirname);
            $temp_dir = $save_dir . '/temp';

            $this->tempDir = $temp_dir;

            // 清理旧数据 start -------------------------------------------------------
            if (File::exists($temp_dir)) {
                File::delete($temp_dir);
            }
            ResourceRepository::delete($video->thumb_for_program);
            ResourceRepository::delete($video->preview);
            foreach ($video->videos as $v)
            {
                ResourceRepository::delete($v->src);
            }
            foreach ($video->video_subtitles as $v)
            {
                ResourceRepository::delete($v->src);
            }
            VideoSrcModel::delByVideoId($video->id);
            // 清理旧数据 end -------------------------------------------------------

            // ......处理新数据
            $merge_video_subtitle               = $video->merge_video_subtitle == 1 && !empty($video->video_subtitles);
            $first_video_subtitle               = $merge_video_subtitle ? $video->video_subtitles[0] : null;
            $first_video_subtitle_resource      = $merge_video_subtitle ? ResourceModel::findByUrlOrPath($first_video_subtitle->src) : null;
            $video_resource                     = ResourceModel::findByUrlOrPath($video->src);
            $video_info                         = FFprobe::create($video_resource->path)->coreInfo();

            $video_simple_preview_config        = my_config('app.video_simple_preview');
            $video_preview_config               = my_config('app.video_preview');
            $video_first_frame_duration         = my_config('app.video_frist_frame_duration');
            $video_subtitle_config              = my_config('app.video_subtitle');

            if (!File::exists($save_dir)) {
                File::mkdir($save_dir, 0777, true);
            }
            if ($video->type === 'pro') {
                // 记录目录
                ResourceRepository::create('' , $save_dir , 'local' , 1 , 0);
                VideoProjectModel::updateById($video->video_project_id , [
                    'directory' => $save_dir ,
                ]);
            }

            if (!File::exists($temp_dir)) {
                File::mkdir($temp_dir, 0777, true);
            }

            $date       = date('Ymd');
            $datetime   = date('YmdHis');

            // 处理文件名称
            $get_video_name = function($type , $name , $index){
                if ($type === 'pro') {
                    // [sprintf 函数可访问右侧链接](https://www.runoob.com/php/func-string-sprintf.html)
                    return empty($name) ? sprintf("%'04s" , $index) : $name;
                }
                return $name;
            };
            $video_name = $get_video_name($video->type , $video->name , $video->index);
            /**
             * 视频第一帧
             */
            $video_first_frame_file = $this->generateRealPath($save_dir , $this->generateMediaSuffix($video->type , $video_name . '【第一帧】' , 'webp'));
            $video_first_frame_url  = FileUtil::generateUrlByRealPath($video_first_frame_file);

            FFmpeg::create()
                ->input($video_resource->path)
                ->ss($video_first_frame_duration, 'input')
                ->frames(1)
                ->quiet()
                ->save($video_first_frame_file);
            // 图片处理
            $image_processor = new ImageProcessor($save_dir);
            $video_first_frame_compress_file = $image_processor->compress($video_first_frame_file , [
                'mode' => 'fix-width' ,
                'width' => 1920 ,
            ] , false);
            File::dFile($video_first_frame_file);
            File::move($video_first_frame_compress_file , $video_first_frame_file);
            VideoModel::updateById($video->id , [
                'thumb_for_program' => $video_first_frame_url ,
            ]);
            ResourceRepository::create($video_first_frame_url , $video_first_frame_file , 'local' , 1 , 0);

            /**
             * 视频简略预览
             */
            $avg_duration           = floor($video_info['duration'] / $video_simple_preview_config['count']);
            $remain_duration        = $video_info['duration'] - $avg_duration * 2;
            $avg_remain_duration    = $remain_duration / $video_simple_preview_config['count'];
            $ts                     = [];
            $input_command          = 'concat:';

            for ($i = 0; $i < $video_simple_preview_config['count']; ++$i)
            {
                $cur_ts         = $temp_dir . '/' . $datetime . random(6, 'letter', true) . '.ts';
                $start_duration = $avg_remain_duration + $avg_remain_duration * $i;

                FFmpeg::create()
                    ->input($video_resource->path)
                    ->ss($start_duration, 'input')
                    ->size($video_simple_preview_config['width'], $video_simple_preview_config['height'])
                    ->disabledAudio()
                    ->quiet()
                    ->duration($video_simple_preview_config['duration'], 'output')
                    ->save($cur_ts);

                $input_command .= $cur_ts . '|';
                $ts[] = $cur_ts;
            }

            $input_command                  = rtrim($input_command, '|');
            $video_simple_preview_file      = $this->generateRealPath($save_dir , $this->generateMediaSuffix($video->type , $video_name . '【预览】' , 'mp4'));
            $video_simple_preview_url       = FileUtil::generateUrlByRealPath($video_simple_preview_file);

            FFmpeg::create()
                ->input($input_command)
                ->quiet()
                ->save($video_simple_preview_file);

            VideoModel::updateById($video->id , [
                'simple_preview'    => $video_simple_preview_url ,
            ]);
            ResourceRepository::create($video_simple_preview_url , $video_simple_preview_file , 'local' , 1 , 0);

            /**
             * 视频完整进度预览
             */
            $determine_duration = function() use($video_info): int
            {
                $duration = $video_info['duration'];
                if ($duration < 1500) {
                    return 3;
                }
                return 5;
            };
            // 自动判断
            $video_preview_config['duration'] = $determine_duration();
            $previews       = [];
            $preview_count  = floor($video_info['duration'] / $video_preview_config['duration']);
            // 图片合成
            $image_width    = $video_preview_config['count'] * $video_preview_config['width'];
            $image_height   = ceil($preview_count / $video_preview_config['count']) * $video_preview_config['height'];

            // 创建透明的图片
            $cav            = imagecreatetruecolor($image_width , $image_height);
            $transparent    = imagecolorallocatealpha($cav,255,255 , 255 , 127);

            imagecolortransparent($cav , $transparent);
            imagefill($cav,0,0 , $transparent);

            for ($i = 0; $i < $preview_count; ++$i)
            {
                $datetime   = date('YmdHis');
                $image      = $temp_dir . '/' . $datetime . random(6 , 'letter' , true) . '.webp';
                $timepoint  = $i * $video_preview_config['duration'];

                FFmpeg::create()
                    ->input($video_resource->path)
                    ->ss($timepoint , 'input')
                    ->size($video_preview_config['width'] , $video_preview_config['width'] / ($video_info['width'] / $video_info['height']))
                    ->frames(1)
                    ->quiet()
                    ->save($image);
                $previews[] = $image;
                $image_cav  = imagecreatefromwebp($image);
                $x          = $i % $video_preview_config['count'] * $video_preview_config['width'];
                $y          = floor($i / $video_preview_config['count']) * $video_preview_config['height'];

                imagecopymerge($cav , $image_cav , $x , $y , 0 , 0 , $video_preview_config['width'] , $video_preview_config['height'] , 100);
            }
            $preview_file   = $this->generateRealPath($save_dir , $this->generateMediaSuffix($video->type , $video_name . '【预览】' ,'jpeg'));
            $preview_url    = FileUtil::generateUrlByRealPath($preview_file);

            // jpeg 最大支持的像素有限！请务必使用 png
            // webp 是 jpeg 的升级版
//            imagewebp($cav , $preview_file , 75);
            imagejpeg($cav , $preview_file , 75);

            VideoModel::updateById($video->id , [
                'preview'           => $preview_url ,
                'preview_width'     => $video_preview_config['width'] ,
                'preview_height'    => $video_preview_config['height'] ,
                'preview_duration'  => $video_preview_config['duration'] ,
                'preview_line_count' => $video_preview_config['count'] ,
                'preview_count'     => $preview_count ,
                'duration'          => $video_info['duration'] ,
                'video_process_status'    => 2 ,
            ]);

            ResourceRepository::create($preview_url , $preview_file , 'local' , 1 , 0);

            /**
             * 视频转码
             */
            $video_transcoding_config   = my_config('app.video_transcoding');
            // 用户设置：是否保留原视频（当存在转码视频的时候就会删除原视频，否则该设置项无效）
            $save_origin_video          = my_config('app.save_origin_video');
            // 用于判断是否有必要保留原视频（不受用户设置影响）
            $save_origin                = true;
            // 是否高清视频
            $is_hd                      = false;
            foreach ($video_transcoding_config['specification'] as $k => $v)
            {
                if ($video_info['width'] < $v['w']) {
                    continue ;
                }
                if ($v['is_hd']) {
                    $is_hd = true;
                }
                $save_origin            = false;
                $transcoded_file        = $this->generateRealPath($save_dir , $this->generateVideoMediaSuffix($video->type , $k , $video->index , $video->name ,'mp4'));
                $transcoded_access_url  = FileUtil::generateUrlByRealPath($transcoded_file);

                $ffmpeg = FFmpeg::create()->input($video_resource->path);
                if ($merge_video_subtitle) {
                    $ffmpeg->subtitle($first_video_subtitle_resource->path);
                }
                $ffmpeg->size($v['w'] , $v['h'])
                    ->codec($video_transcoding_config['codec'] , 'video')
                    ->quiet()
                    ->save($transcoded_file);
                $info = FFprobe::create($transcoded_file)->coreInfo();
                VideoSrcModel::insert([
                    'video_id'      => $video->id ,
                    'src'           =>  $transcoded_access_url ,
                    'duration'      => $info['duration'] ,
                    'width'         => $info['width'] ,
                    'height'        => $info['height'] ,
                    'size'          => $info['size'] ,
                    'definition'    => $k ,
                    'created_at'   => date('Y-m-d H:i:s') ,
                ]);
                ResourceRepository::create($transcoded_access_url , $transcoded_file , 'local' , 1 , 0);
            }

            if ($is_hd) {
                VideoModel::updateById($video->id , [
                    'is_hd' => 1
                ]);
            }

            if ($save_origin_video || $save_origin) {
                $definition             = '原画';
                $transcoded_file        = $this->generateRealPath($save_dir , $this->generateVideoMediaSuffix($video->type , $definition , $video->index , $video->name ,'mp4'));
                $transcoded_access_url  = FileUtil::generateUrlByRealPath($transcoded_file);
                $ffmpeg = FFmpeg::create()
                    ->input($video_resource->path)
                    ->quiet();
                if ($merge_video_subtitle) {
                    $ffmpeg->subtitle($first_video_subtitle_resource->path);
                }
                $ffmpeg->save($transcoded_file);
                $video_info = FFprobe::create($transcoded_file)->coreInfo();
                VideoSrcModel::insert([
                    'video_id'      => $video->id ,
                    'src'           => $transcoded_access_url ,
                    'duration'      => $video_info['duration'] ,
                    'width'         => $video_info['width'] ,
                    'height'        => $video_info['height'] ,
                    'size'          => $video_info['size'] ,
                    'definition'    => $definition ,
                    'created_at'   => date('Y-m-d H:i:s') ,
                ]);
                // 删除源文件
                ResourceRepository::delete($video->src);
                ResourceRepository::create($transcoded_access_url , $transcoded_file , 'local' , 1 , 0);
                // 更新原视频
                VideoModel::updateById($video->id , [
                    'src' => $transcoded_access_url ,
                ]);
            } else {
                // 删除原视频文件
                ResourceRepository::delete($video->src);
            }

            if ($merge_video_subtitle) {
                // 字幕合成完毕后删除字幕
                foreach ($video->video_subtitles as $v)
                {
                    ResourceRepository::delete($v->src);
                }
                VideoSubtitleModel::delByVideoId($video->id);
            } else {
                // 字幕转换
                foreach ($video->video_subtitles as $v)
                {
                    $video_subtitle_resource = ResourceModel::findByUrlOrPath($v->src);
                    if (!File::exists($video_subtitle_resource->path)) {
                        // 字幕文件不存在，跳过
                        continue ;
                    }
                    $video_subtitle_convert_file        = $this->generateRealPath($save_dir , $this->generateMediaSuffix($video->type , "{$video_name}【{$v->name}】" , 'vtt'));
                    $video_subtitle_convert_access_url  = FileUtil::generateUrlByRealPath($video_subtitle_convert_file);
                    FFmpeg::create()
                        ->input($video_subtitle_resource->path)
                        ->quiet()
                        ->save($video_subtitle_convert_file);
                    VideoSubtitleModel::updateById($v->id , [
                        'src' => $video_subtitle_convert_access_url
                    ]);
                    ResourceRepository::delete($v->src);
                    ResourceRepository::create($video_subtitle_convert_access_url , $video_subtitle_convert_file , 'local' , 1 , 0);
                }
            }
            File::delete($temp_dir);
        }
        VideoModel::updateById($this->videoId , [
            'video_process_status' => 3
        ]);
    }

    // 生成媒体的后缀
    private function generateMediaSuffix(string $type , string $name , string $extension): string
    {
        return $type === 'pro' ? $name . '.' . $extension : $name . '【' . random(8 , 'letter' , true) . '】' . '.' . $extension;
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

    public function failed(Exception $e)
    {
        VideoModel::updateById($this->videoId , [
            'video_process_status' => -1 ,
        ]);
        // 删除临时处理目录
        if (File::exists($this->tempDir)) {
            File::delete($this->tempDir);
        }
    }
}
