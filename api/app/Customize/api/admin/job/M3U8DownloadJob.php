<?php


namespace App\Customize\api\admin\job;


use Core\Lib\M3U8;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use stdClass;

class M3U8DownloadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var stdClass
     */
    private $context;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(stdClass $context)
    {
        $this->context = $context;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $m3u8 = new M3U8($this->context->src, [
            'url' => $this->context->url,
            'proxy_pass' => $this->context->proxy_pass,
        ]);
        // 下载任务
        $m3u8->download($this->context->save_dir, $this->context->definition, $this->context->filename);
    }

}
