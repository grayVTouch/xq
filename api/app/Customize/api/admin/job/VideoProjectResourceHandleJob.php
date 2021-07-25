<?php

namespace App\Customize\api\admin\job;

use App\Customize\api\admin\handler\VideoProjectHandler;
use App\Customize\api\admin\job\handler\VideoHandler;
use App\Customize\api\admin\job\middleware\BootMiddleware;
use App\Customize\api\admin\job\traits\FileTrait;
use App\Customize\api\admin\model\ImageModel;
use App\Customize\api\admin\model\VideoModel;
use App\Customize\api\admin\model\ResourceModel;
use App\Customize\api\admin\model\VideoProjectModel;
use App\Customize\api\admin\model\VideoSrcModel;
use App\Customize\api\admin\model\VideoSubtitleModel;
use App\Customize\api\admin\repository\FileRepository;
use App\Customize\api\admin\repository\ResourceRepository;
use App\Customize\api\admin\repository\StorageRepository;
use Core\Lib\File;
use Core\Wrapper\FFmpeg;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use stdClass;
use function api\admin\my_config;
use function core\get_extension;
use function core\random;

class VideoProjectResourceHandleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private $videoProjectId = 0;

    /**
     * @var string
     */
    private $originalName = '';

    /**
     * @var stdClass
     */
    private $videoProject = null;

    /**
     * @var string
     */
    private $saveDir = '';

    /**
     * @var string
     */
    private $relativeDir = '';

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
                'file_process_message' => '处理成功' ,
            ]);
            return ;
        }
        if (empty($this->originalName)) {
            // 添加动作
            VideoProjectModel::updateById($video_project->id , [
                'file_process_status' => 2 ,
                'file_process_message' => '处理成功' ,
            ]);
            return ;
        }
        VideoProjectModel::updateById($video_project->id , [
            // 处理中
            'file_process_status' => 1 ,
        ]);

        VideoProjectHandler::videos($video_project);
        foreach ($video_project->videos as $v)
        {
            VideoModel::updateById($v->id , [
                'file_process_status' => 0 ,
            ]);
            // 视频处理
            (new VideoHandler($v->id))
                ->handle();
        }
        VideoProjectModel::updateById($video_project->id , [
            // 处理已完成
            'file_process_status' => 2 ,
            'file_process_message' => '处理成功' ,
        ]);
    }

    // 任务执行失败的时候提示的错误信息
    public function failed(Exception $e)
    {
        VideoProjectModel::updateById($this->videoProjectId , [
            // 处理失败
            'file_process_status' => -1 ,
            'file_process_message' => $e->getMessage() ,
            'file_process_data' => $e->getTraceAsString() ,
        ]);
    }
}
