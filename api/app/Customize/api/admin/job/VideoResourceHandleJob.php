<?php

namespace App\Customize\api\admin\job;


use App\Customize\api\admin\job\handler\VideoHandler;
use App\Customize\api\admin\job\middleware\BootMiddleware;
use App\Customize\api\admin\model\VideoModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VideoResourceHandleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private $videoId = 0;

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
        $video_handler = new VideoHandler($this->videoId);
        $video_handler->handle();
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
