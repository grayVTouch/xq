<?php

namespace App\Customize\api\admin\job;

use App\Customize\api\admin\facade\AliyunOss;
use App\Customize\api\admin\handler\VideoHandler;
use App\Customize\api\admin\job\middleware\BootMiddleware;
use App\Customize\api\admin\job\traits\FileTrait;
use App\Customize\api\admin\job\traits\VideoTrait;
use App\Customize\api\admin\model\ResourceModel;
use App\Customize\api\admin\model\SystemSettingsModel;
use App\Customize\api\admin\model\VideoModel;
use App\Customize\api\admin\model\VideoProjectModel;
use App\Customize\api\admin\model\VideoSrcModel;
use App\Customize\api\admin\model\VideoSubtitleModel;
use App\Customize\api\admin\repository\ResourceRepository;
use App\Customize\api\admin\repository\StorageRepository;
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
use App\Customize\api\admin\repository\FileRepository;
use function api\admin\my_config;
use function core\random;
use function core\detect_encoding;

class VideoHandleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    use FileTrait;
    use VideoTrait;


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
        // 附加：视频
        VideoHandler::videos($video);
        // 附加：字幕
        VideoHandler::videoSubtitles($video);

        $relative_dir = StorageRepository::getRelativeDirectoryByVideoId($video->id);
        $save_dir = FileRepository::generateRealPathByWithoutPrefixRelativePath($relative_dir);
        $temp_dir = $save_dir . '/temp';

        $this->relativeDir = $relative_dir;
        $this->saveDir = $save_dir;
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

        $this->settings = SystemSettingsModel::first();
        $this->video = $video;

        if ($video->disk === 'local') {
            $this->localHandle();
        } else if ($video->disk === 'aliyun') {
            $this->aliyunCloudHandle();
        } else {
            // todo
        }

        if ($video->disk === 'cloud') {
            File::delete($save_dir);
        } else {
            File::delete($temp_dir);
        }

        VideoModel::updateById($this->videoId , [
            'video_process_status' => 3 ,
            'video_process_message' => '处理成功' ,
        ]);
    }

    // 阿里云存储
    public function aliyunCloudHandle()
    {
        $video_resource = ResourceModel::findByUrlOrPath($this->video->src);
        if ($video_resource->disk !== 'local') {
            throw new Exception('当前视频已无法再次处理！源视频已经上传到云存储');
        }
        // ......处理新数据
        $merge_video_subtitle               = $this->video->merge_video_subtitle == 1 && !empty($this->video->video_subtitles);
        $first_video_subtitle               = $merge_video_subtitle ? $this->video->video_subtitles[0] : null;
        $first_video_subtitle_resource      = $merge_video_subtitle ? ResourceModel::findByUrlOrPath($first_video_subtitle->src) : null;
        $video_info                         = FFprobe::create($video_resource->path)->coreInfo();

        $video_simple_preview_config        = my_config('app.video_simple_preview');
        $video_preview_config               = my_config('app.video_preview');
        $video_subtitle_config              = my_config('app.video_subtitle');
        $video_first_frame_config           = my_config('app.video_first_frame');

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
        $video_name = $get_video_name($this->video->type , $this->video->name , $this->video->index);

        /**
         * ************************
         * 字幕文件编码转换
         * ************************
         */
        if ($merge_video_subtitle) {
            $origin_str = file_get_contents($first_video_subtitle_resource->path);
            $from_encoding = detect_encoding($origin_str);
            $to_encoding = 'UTF-8';
            if ($from_encoding !== $to_encoding) {
                $convert_str = mb_convert_encoding($origin_str , $to_encoding , $from_encoding);
                // 覆盖内容
                file_put_contents($first_video_subtitle_resource->path , $convert_str);
            }
        }

        /**
         * *****************************************
         * 视频第一帧
         * *****************************************
         */
        $relative_first_frame_file  = $this->generateMediaSuffix($this->video->type , $video_name . '【第一帧】' , 'webp');
        $aliyun_first_frame_file    = $this->relativeDir . '/' . $relative_first_frame_file;
        $video_first_frame_file     = $this->generateRealPath($this->saveDir , $relative_first_frame_file);
        if (File::exists($video_first_frame_file)) {
            File::delete($video_first_frame_file);
        }
        FFmpeg::create()
            ->input($video_resource->path)
            ->ss($video_first_frame_config['duration'], 'input')
            ->frames(1)
            ->save($video_first_frame_file);
        ResourceRepository::create('' , $video_first_frame_file , 'local' , 0 , 0);
        // 图片处理
        $image_processor = new ImageProcessor($this->saveDir);
        $video_first_frame_compress_file = $image_processor->compress($video_first_frame_file , [
            'mode' => 'fix-width' ,
            'width' => $video_first_frame_config['width'] ,
        ] , false);
        ResourceRepository::create('' , $video_first_frame_compress_file , 'local' , 0 , 0);
        $video_first_frame_upload_res = AliyunOss::upload($this->settings->aliyun_bucket , $aliyun_first_frame_file , $video_first_frame_compress_file);
        if ($video_first_frame_upload_res['data'] > 0) {
            throw new Exception($video_first_frame_upload_res['message']);
        }
        VideoModel::updateById($this->video->id , [
            'thumb_for_program' => $video_first_frame_upload_res['data'] ,
        ]);
        ResourceRepository::delete($video_first_frame_file);
        ResourceRepository::delete($video_first_frame_compress_file);
        ResourceRepository::createAliyun($video_first_frame_upload_res['data'] , $this->settings->aliyun_bucket , 1 , 0);
        File::delete($video_first_frame_file);
        File::delete($video_first_frame_compress_file);


        /**
         * *****************************************
         * 视频简略预览
         * *****************************************
         */
        $avg_duration           = floor($video_info['duration'] / $video_simple_preview_config['count']);
        $remain_duration        = $video_info['duration'] - $avg_duration * 2;
        $avg_remain_duration    = $remain_duration / $video_simple_preview_config['count'];
        $ts                     = [];
        $input_command          = 'concat:';

        for ($i = 0; $i < $video_simple_preview_config['count']; ++$i)
        {
            $cur_ts         = $this->tempDir . '/' . $datetime . random(6, 'letter', true) . '.ts';
            $start_duration = $avg_remain_duration + $avg_remain_duration * $i;
            if (File::exists($cur_ts)) {
                File::delete($cur_ts);
            }
            FFmpeg::create()
                ->input($video_resource->path)
                ->ss($start_duration, 'input')
                ->size($video_simple_preview_config['width'], $video_simple_preview_config['height'])
                ->disabledAudio()
                ->duration($video_simple_preview_config['duration'], 'output')
                ->save($cur_ts);

            $input_command .= $cur_ts . '|';
            $ts[] = $cur_ts;
        }
        $input_command                  = rtrim($input_command, '|');
        $relative_simple_preview_file   = $this->generateMediaSuffix($this->video->type , $video_name . '【预览】' , 'mp4');
        $aliyun_simple_preview_file     = $this->relativeDir . '/' . $this->generateMediaSuffix($this->video->type , $video_name . '【预览】' , 'mp4');
        $video_simple_preview_file      = $this->generateRealPath($this->saveDir , $relative_simple_preview_file);
        if (File::exists($video_simple_preview_file)) {
            File::delete($video_simple_preview_file);
        }
        FFmpeg::create()
            ->input($input_command)
            ->save($video_simple_preview_file);
        ResourceRepository::create('' , $video_simple_preview_file , 'local' , 0 , 0);
        $simple_preview_upload_res = AliyunOss::upload($this->settings->aliyun_bucket , $aliyun_simple_preview_file , $video_simple_preview_file);
        if ($simple_preview_upload_res['data'] > 0) {
            throw new Exception($simple_preview_upload_res['message']);
        }
        VideoModel::updateById($this->video->id , [
            'simple_preview'    => $simple_preview_upload_res['data'] ,
        ]);
        ResourceRepository::delete($video_simple_preview_file);
        ResourceRepository::createAliyun($simple_preview_upload_res['data'] , $this->settings->aliyun_bucket , 1 , 0);
        File::delete($video_simple_preview_file);

        /**
         * *****************************************
         * 视频完整进度预览
         * *****************************************
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
            $image      = $this->tempDir . '/' . $datetime . random(6 , 'letter' , true) . '.webp';
            $timepoint  = $i * $video_preview_config['duration'];
            if (File::exists($image)) {
                File::delete($image);
            }
            FFmpeg::create()
                ->input($video_resource->path)
                ->ss($timepoint , 'input')
                ->size($video_preview_config['width'] , $video_preview_config['width'] / ($video_info['width'] / $video_info['height']))
                ->frames(1)
                ->save($image);
            $image_cav  = imagecreatefromwebp($image);
            $x          = $i % $video_preview_config['count'] * $video_preview_config['width'];
            $y          = floor($i / $video_preview_config['count']) * $video_preview_config['height'];

            imagecopymerge($cav , $image_cav , $x , $y , 0 , 0 , $video_preview_config['width'] , $video_preview_config['height'] , 100);
        }
        $relative_preview_file  = $this->generateMediaSuffix($this->video->type , $video_name . '【预览】' ,'jpeg');
        $aliyun_preview_file    = $this->relativeDir . '/' . $relative_preview_file;
        $preview_file           = $this->generateRealPath($this->saveDir , $relative_preview_file);
        if (File::exists($preview_file)) {
            File::delete($preview_file);
        }
        imagejpeg($cav , $preview_file , 75);
        ResourceRepository::create('' , $preview_file , 'local' , 0 , 0);
        $preview_upload_res = AliyunOss::upload($this->settings->aliyun_bucket , $aliyun_preview_file , $preview_file);
        if ($preview_upload_res['data'] > 0) {
            throw new Exception($preview_upload_res['message']);
        }
        VideoModel::updateById($this->video->id , [
            'preview'           => $preview_upload_res['data'] ,
            'preview_width'     => $video_preview_config['width'] ,
            'preview_height'    => $video_preview_config['height'] ,
            'preview_duration'  => $video_preview_config['duration'] ,
            'preview_line_count' => $video_preview_config['count'] ,
            'preview_count'     => $preview_count ,
            'duration'          => $video_info['duration'] ,
            'video_process_status'    => 2 ,
        ]);
        ResourceRepository::delete($preview_file);
        ResourceRepository::createAliyun($preview_upload_res['data'] , $this->settings->aliyun_bucket , 1 , 0);
        File::delete($preview_file);

        /**
         * *****************************************
         * 视频转码
         * *****************************************
         */
        $video_transcoding_config   = my_config('app.video_transcoding');
        // 用户设置：是否保留原视频（当存在转码视频的时候就会删除原视频，否则该设置项无效）
        $save_origin_video          = my_config('app.save_origin_video');
        // 用于判断是否有必要保留原视频（不受用户设置影响）
        $save_origin                = true;
        // 是否高清视频
        $is_hd = false;
        $max_definition = null;
        // 从小到大 - 排序
        $video_transcode_specification = $video_transcoding_config['specification'];

        // 从小到大 - 排序
        usort($video_transcode_specification , function($a , $b){
            if ($a['w'] === $b['w']) {
                return 0;
            }
            return $a['w'] < $b['w'] ? 1 : -1;
        });
        $video_transcode_specification = array_filter($video_transcode_specification , function($v) use($video_info){
            return $video_info['width'] >= $v['w'];
        });
        if (count($video_transcode_specification) > 0) {
            $max_video_transcode_specification = $video_transcode_specification[0];
        }
        foreach ($video_transcoding_config['specification'] as $k => $v)
        {
            if ($video_info['width'] < $v['w']) {
                continue ;
            }
            if ($max_video_transcode_specification['w'] === $v['w']) {
                $max_definition = [
                    'name'       => $k ,
                    'definition' => $v ,
                ];
            }
            if ($v['is_hd']) {
                $is_hd = true;
            }
            $save_origin            = false;
            $relative_transcoded_file = $this->generateVideoMediaSuffix($this->video->type , $k , $this->video->index , $this->video->name ,'mp4');
            $aliyun_transcoded_file  = $this->relativeDir . '/' . $relative_transcoded_file;
            $transcoded_file        = $this->generateRealPath($this->saveDir , $relative_transcoded_file);
            if (File::exists($transcoded_file)) {
                File::delete($transcoded_file);
            }
            $ffmpeg = FFmpeg::create()->input($video_resource->path);
            if ($merge_video_subtitle) {
                $ffmpeg->subtitle($first_video_subtitle_resource->path);
            }
            $ffmpeg->size($v['w'] , $v['h'])
                ->codec($video_transcoding_config['codec'] , 'video')
                ->save($transcoded_file);
            ResourceRepository::create('' , $transcoded_file , 'local' , 0 , 0);
            $transcode_file_upload_res = AliyunOss::upload($this->settings->aliyun_bucket , $aliyun_transcoded_file , $transcoded_file);
            if ($transcode_file_upload_res['data'] > 0) {
                throw new Exception($transcode_file_upload_res['message']);
            }
            $info = FFprobe::create($transcoded_file)->coreInfo();
            VideoSrcModel::insert([
                'video_id'      => $this->video->id ,
                'src'           => $transcode_file_upload_res['data'] ,
                'duration'      => $info['duration'] ,
                'width'         => $info['width'] ,
                'height'        => $info['height'] ,
                'size'          => $info['size'] ,
                'definition'    => $k ,
                'created_at'   => date('Y-m-d H:i:s') ,
            ]);
            ResourceRepository::delete($transcoded_file);
            ResourceRepository::createAliyun($transcode_file_upload_res['data'] , $this->settings->aliyun_bucket , 1 , 0);
            File::delete($transcoded_file);
        }

        if (!empty($max_definition)) {
            $max_definition_name = $max_definition['name'];
            if ($max_definition['definition']['w'] < $video_info['width']) {
                $max_definition_name = '原画';
            }
            VideoModel::updateById($this->video->id , [
                'max_definition' => $max_definition_name ,
            ]);
        }

        if ($is_hd) {
            VideoModel::updateById($this->video->id , [
                'is_hd' => 1
            ]);
        }

        if ($save_origin_video || $save_origin) {
            $definition             = '原画';
            $relative_transcoded_file = $this->generateVideoMediaSuffix($this->video->type , $definition , $this->video->index , $this->video->name ,'mp4');
            $aliyun_transcoded_file  = $this->relativeDir . '/' . $relative_transcoded_file;
            $transcoded_file        = $this->generateRealPath($this->saveDir , $relative_transcoded_file);
            if (File::exists($transcoded_file)) {
                File::delete($transcoded_file);
            }
            $ffmpeg = FFmpeg::create()
                ->input($video_resource->path)
                ->codec($video_transcoding_config['codec'] , 'video');
            if ($merge_video_subtitle) {
                $ffmpeg->subtitle($first_video_subtitle_resource->path);
            }
            $ffmpeg->save($transcoded_file);
            ResourceRepository::create('' , $transcoded_file , 'local' , 0 , 0);
            $transcode_file_upload_res = AliyunOss::upload($this->settings->aliyun_bucket , $aliyun_transcoded_file , $transcoded_file);
            if ($transcode_file_upload_res['data'] > 0) {
                throw new Exception($transcode_file_upload_res['message']);
            }
            $video_info = FFprobe::create($transcoded_file)->coreInfo();
            VideoSrcModel::insert([
                'video_id'      => $this->video->id ,
                'src'           => $transcode_file_upload_res['data'] ,
                'duration'      => $video_info['duration'] ,
                'width'         => $video_info['width'] ,
                'height'        => $video_info['height'] ,
                'size'          => $video_info['size'] ,
                'definition'    => $definition ,
                'created_at'   => date('Y-m-d H:i:s') ,
            ]);
            // 更新原视频
            VideoModel::updateById($this->video->id , [
                'src' => $transcode_file_upload_res['data'] ,
            ]);
            // 删除源文件
            ResourceRepository::delete($this->video->src);
            ResourceRepository::delete($transcoded_file);
            ResourceRepository::createAliyun($transcode_file_upload_res['data'] , $this->settings->aliyun_bucket , 1 , 0);

            File::delete($transcoded_file);
        } else {
            // 删除原视频文件
            ResourceRepository::delete($this->video->src);
        }

        if ($merge_video_subtitle) {
            // 字幕合成完毕后删除字幕
            foreach ($this->video->video_subtitles as $v)
            {
                ResourceRepository::delete($v->src);
            }
            VideoSubtitleModel::delByVideoId($this->video->id);
        } else {
            // 字幕转换
            foreach ($this->video->video_subtitles as $v)
            {
                $video_subtitle_resource = ResourceModel::findByUrlOrPath($v->src);
                if (!File::exists($video_subtitle_resource->path)) {
                    // 字幕文件不存在，跳过
                    continue ;
                }
                $relative_video_subtitle_convert_file = $this->generateMediaSuffix($this->video->type , "{$video_name}【{$v->name}】" , $video_subtitle_config['extension']);
                $aliyun_video_subtitle_convert_file = $this->relativeDir . '/' . $relative_video_subtitle_convert_file;
                $video_subtitle_convert_file        = $this->generateRealPath($this->saveDir , $relative_video_subtitle_convert_file);
                if (File::exists($video_subtitle_convert_file)) {
                    File::delete($video_subtitle_convert_file);
                }
                FFmpeg::create()
                    ->input($video_subtitle_resource->path)
                    ->save($video_subtitle_convert_file);
                ResourceRepository::create('' , $video_subtitle_convert_file , 'local' , 0 , 0);
                $video_subtitle_upload_res = AliyunOss::upload($this->settings->aliyun_bucket , $aliyun_video_subtitle_convert_file , $video_subtitle_convert_file);
                if ($video_subtitle_upload_res['data'] > 0) {
                    throw new Exception($video_subtitle_upload_res['message']);
                }
                VideoSubtitleModel::updateById($v->id , [
                    'src' => $video_subtitle_upload_res['data'] ,
                ]);
                ResourceRepository::delete($video_subtitle_convert_file);
                ResourceRepository::delete($v->src);
                ResourceRepository::createAliyun($video_subtitle_upload_res['data'] , $this->settings->aliyun_bucket , 1 , 0);
                File::delete($video_subtitle_convert_file);
            }
        }
    }

    // 本地存储
    public function localHandle()
    {
        $video_resource = ResourceModel::findByUrlOrPath($this->video->src);

        if ($video_resource->disk !== 'local') {
            throw new Exception('当前视频已无法再次处理！源视频已经上传到云存储');
        }

        // ......处理新数据
        $merge_video_subtitle               = $this->video->merge_video_subtitle == 1 && !empty($this->video->video_subtitles);
        $first_video_subtitle               = $merge_video_subtitle ? $this->video->video_subtitles[0] : null;
        $first_video_subtitle_resource      = $merge_video_subtitle ? ResourceModel::findByUrlOrPath($first_video_subtitle->src) : null;
        $video_info                         = FFprobe::create($video_resource->path)->coreInfo();

        $video_simple_preview_config        = my_config('app.video_simple_preview');
        $video_preview_config               = my_config('app.video_preview');
        $video_subtitle_config              = my_config('app.video_subtitle');
        $video_first_frame_config           = my_config('app.video_first_frame');

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
        $video_name = $get_video_name($this->video->type , $this->video->name , $this->video->index);

        /**
         * ************************
         * 字幕文件编码转换
         * ************************
         */
        if ($merge_video_subtitle) {
            $origin_str = file_get_contents($first_video_subtitle_resource->path);
            $from_encoding = detect_encoding($origin_str);
            $to_encoding = 'UTF-8';
            if ($from_encoding !== $to_encoding) {
                $convert_str = mb_convert_encoding($origin_str , $to_encoding , $from_encoding);
                // 覆盖内容
                file_put_contents($first_video_subtitle_resource->path , $convert_str);
            }
        }
        /**
         * 视频第一帧
         */
        $video_first_frame_file = $this->generateRealPath($this->saveDir , $this->generateMediaSuffix($this->video->type , $video_name . '【第一帧】' , 'webp'));
        $video_first_frame_url  = FileRepository::generateUrlByRealPath($video_first_frame_file);

        if (File::exists($video_first_frame_file)) {
            File::delete($video_first_frame_file);
        }
        FFmpeg::create()
            ->input($video_resource->path)
            ->ss($video_first_frame_config['duration'], 'input')
            ->frames(1)
            ->save($video_first_frame_file);
        // 图片处理
        $image_processor = new ImageProcessor($this->saveDir);
        $video_first_frame_compress_file = $image_processor->compress($video_first_frame_file , [
            'mode' => 'fix-width' ,
            'width' => $video_first_frame_config['width'] ,
        ] , false);
        File::dFile($video_first_frame_file);
        File::move($video_first_frame_compress_file , $video_first_frame_file);
        VideoModel::updateById($this->video->id , [
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
            $cur_ts         = $this->tempDir . '/' . $datetime . random(6, 'letter', true) . '.ts';
            $start_duration = $avg_remain_duration + $avg_remain_duration * $i;

            if (File::exists($cur_ts)) {
                File::delete($cur_ts);
            }
            FFmpeg::create()
                ->input($video_resource->path)
                ->ss($start_duration, 'input')
                ->size($video_simple_preview_config['width'], $video_simple_preview_config['height'])
                ->disabledAudio()
                ->duration($video_simple_preview_config['duration'], 'output')
                ->save($cur_ts);

            $input_command .= $cur_ts . '|';
            $ts[] = $cur_ts;
        }

        $input_command                  = rtrim($input_command, '|');
        $video_simple_preview_file      = $this->generateRealPath($this->saveDir , $this->generateMediaSuffix($this->video->type , $video_name . '【预览】' , 'mp4'));
        $video_simple_preview_url       = FileRepository::generateUrlByRealPath($video_simple_preview_file);

        if (File::exists($video_simple_preview_file)) {
            File::delete($video_simple_preview_file);
        }
        FFmpeg::create()
            ->input($input_command)
            ->save($video_simple_preview_file);

        VideoModel::updateById($this->video->id , [
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
            $image      = $this->tempDir . '/' . $datetime . random(6 , 'letter' , true) . '.webp';
            $timepoint  = $i * $video_preview_config['duration'];

            if (File::exists($image)) {
                File::delete($image);
            }
            FFmpeg::create()
                ->input($video_resource->path)
                ->ss($timepoint , 'input')
                ->size($video_preview_config['width'] , $video_preview_config['width'] / ($video_info['width'] / $video_info['height']))
                ->frames(1)
                ->save($image);
            $previews[] = $image;
            $image_cav  = imagecreatefromwebp($image);
            $x          = $i % $video_preview_config['count'] * $video_preview_config['width'];
            $y          = floor($i / $video_preview_config['count']) * $video_preview_config['height'];

            imagecopymerge($cav , $image_cav , $x , $y , 0 , 0 , $video_preview_config['width'] , $video_preview_config['height'] , 100);
        }
        $preview_file   = $this->generateRealPath($this->saveDir , $this->generateMediaSuffix($this->video->type , $video_name . '【预览】' ,'jpeg'));
        $preview_url    = FileRepository::generateUrlByRealPath($preview_file);

        if (File::exists($preview_file)) {
            File::delete($preview_file);
        }

        // jpeg 最大支持的像素有限！请务必使用 png
        // webp 是 jpeg 的升级版
//            imagewebp($cav , $preview_file , 75);
        imagejpeg($cav , $preview_file , 75);

        VideoModel::updateById($this->video->id , [
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
        $is_hd = false;
        $max_definition = null;
        $video_transcode_specification = $video_transcoding_config['specification'];

        // 从小到大 - 排序
        usort($video_transcode_specification , function($a , $b){
            if ($a['w'] === $b['w']) {
                return 0;
            }
            return $a['w'] < $b['w'] ? 1 : -1;
        });
        $video_transcode_specification = array_filter($video_transcode_specification , function($v) use($video_info){
            return $video_info['width'] >= $v['w'];
        });
        if (count($video_transcode_specification) > 0) {
            $max_video_transcode_specification = $video_transcode_specification[0];
        }
        foreach ($video_transcoding_config['specification'] as $k => $v)
        {
            if ($video_info['width'] < $v['w']) {
                continue ;
            }
            if ($max_video_transcode_specification['w'] === $v['w']) {
                $max_definition = [
                    'name'       => $k ,
                    'definition' => $v ,
                ];
            }
            if ($v['is_hd']) {
                $is_hd = true;
            }
            $save_origin            = false;
            $transcoded_file        = $this->generateRealPath($this->saveDir , $this->generateVideoMediaSuffix($this->video->type , $k , $this->video->index , $this->video->name ,'mp4'));
            $transcoded_access_url  = FileRepository::generateUrlByRealPath($transcoded_file);

            if (File::exists($transcoded_file)) {
                File::delete($transcoded_file);
            }

            $ffmpeg = FFmpeg::create()->input($video_resource->path);
            if ($merge_video_subtitle) {
                $ffmpeg->subtitle($first_video_subtitle_resource->path);
            }
            $ffmpeg->size($v['w'] , $v['h'])
                ->codec($video_transcoding_config['codec'] , 'video')
                ->save($transcoded_file);
            $info = FFprobe::create($transcoded_file)->coreInfo();
            VideoSrcModel::insert([
                'video_id'      => $this->video->id ,
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

        if (!empty($max_definition)) {
            $max_definition_name = $max_definition['name'];
            if ($max_definition['definition']['w'] < $video_info['width']) {
                $max_definition_name = '原画';
            }
            VideoModel::updateById($this->video->id , [
                'max_definition' => $max_definition_name ,
            ]);
        }

        if ($is_hd) {
            VideoModel::updateById($this->video->id , [
                'is_hd' => 1
            ]);
        }

        if ($save_origin_video || $save_origin) {
            $definition             = '原画';
            $transcoded_file        = $this->generateRealPath($this->saveDir , $this->generateVideoMediaSuffix($this->video->type , $definition , $this->video->index , $this->video->name ,'mp4'));
            $transcoded_access_url  = FileRepository::generateUrlByRealPath($transcoded_file);
            if ($video_resource->path !== $transcoded_file) {
                if (File::exists($transcoded_file)) {
                    File::delete($transcoded_file);
                }
                $ffmpeg = FFmpeg::create()
                    ->input($video_resource->path)
                    ->codec($video_transcoding_config['codec'] , 'video');
                if ($merge_video_subtitle) {
                    $ffmpeg->subtitle($first_video_subtitle_resource->path);
                }
                $ffmpeg->save($transcoded_file);
                $video_info = FFprobe::create($transcoded_file)->coreInfo();
                VideoSrcModel::insert([
                    'video_id'      => $this->video->id ,
                    'src'           => $transcoded_access_url ,
                    'duration'      => $video_info['duration'] ,
                    'width'         => $video_info['width'] ,
                    'height'        => $video_info['height'] ,
                    'size'          => $video_info['size'] ,
                    'definition'    => $definition ,
                    'created_at'   => date('Y-m-d H:i:s') ,
                ]);
                // 删除源文件
                ResourceRepository::delete($this->video->src);
                ResourceRepository::create($transcoded_access_url , $transcoded_file , 'local' , 1 , 0);
                // 更新原视频
                VideoModel::updateById($this->video->id , [
                    'src' => $transcoded_access_url ,
                ]);
            }
        } else {
            // 删除原视频文件
            ResourceRepository::delete($this->video->src);
        }

        if ($merge_video_subtitle) {
            // 字幕合成完毕后删除字幕
            foreach ($this->video->video_subtitles as $v)
            {
                ResourceRepository::delete($v->src);
            }
            VideoSubtitleModel::delByVideoId($this->video->id);
        } else {
            // 字幕转换
            foreach ($this->video->video_subtitles as $v)
            {
                $video_subtitle_resource = ResourceModel::findByUrlOrPath($v->src);
                if (!File::exists($video_subtitle_resource->path)) {
                    // 字幕文件不存在，跳过
                    continue ;
                }
                $video_subtitle_convert_file        = $this->generateRealPath($this->saveDir , $this->generateMediaSuffix($this->video->type , "{$video_name}【{$v->name}】" , $video_subtitle_config['extension']));
                $video_subtitle_convert_access_url  = FileRepository::generateUrlByRealPath($video_subtitle_convert_file);
                if (File::exists($video_subtitle_convert_file)) {
                    File::delete($video_subtitle_convert_file);
                }
                FFmpeg::create()
                    ->input($video_subtitle_resource->path)
                    ->save($video_subtitle_convert_file);
                VideoSubtitleModel::updateById($v->id , [
                    'src' => $video_subtitle_convert_access_url
                ]);
                ResourceRepository::delete($v->src);
                ResourceRepository::create($video_subtitle_convert_access_url , $video_subtitle_convert_file , 'local' , 1 , 0);
            }
        }
    }

    public function failed(Exception $e)
    {
        VideoModel::updateById($this->videoId , [
            'video_process_status' => -1 ,
            'video_process_message' => $e->getMessage() ,
            'video_process_data' => $e->getTraceAsString() ,
        ]);
        // 删除临时处理目录
        if (File::exists($this->tempDir)) {
            File::delete($this->tempDir);
        }
    }
}
