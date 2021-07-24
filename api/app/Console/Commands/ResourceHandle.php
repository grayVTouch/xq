<?php

namespace App\Console\Commands;

use App\Customize\api\admin\facade\AliyunOss;
use App\Model\DiskModel;
use App\Model\ResourceModel;
use App\Model\TimerTaskLogModel;
use Core\Lib\File;

class ResourceHandle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '从本地磁盘删除无效资源，释放磁盘空间';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    // 单次清理记录数
    public $limit = 1000;

    // 间隔清理时间（单位 us 微秒）
    public $interval = 100 * 1000;

    // 未删除但也未被使用的记录保存多长时间，单位: s
    public $duration = 2 * 24 * 3600;

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $start_time = date('Y-m-d H:i:s');
        $timer_task_log = "【start: {$start_time}】 资源清理任务开始执行...";
        $res = ResourceModel::getWaitDeleteByLimitIdAndSize(0 , $this->limit);
        $time = time();
        $loop_count = 0;
        echo $timer_task_log . PHP_EOL;
        $total_count = 0;
        $failed_count = 0;
        $deleted_count = 0;
        $datetime = date('Y-m-d H:i:s' , $time);
        while (!$res->isEmpty())
        {
            $loop_count++;
            $last = null;
            $progress_num = 100;
            $fill_repeat = str_repeat('|' , 0);
            $vprintf = "【第%s轮】 [%-{$progress_num}s] %s%%\r";
            printf($vprintf , $loop_count , $fill_repeat , 0);
            foreach ($res as $k => $v)
            {
                $destroy_ratio = ($k + 1) / $res->count();
                $fill_repeat = str_repeat('|' , $progress_num * $destroy_ratio);
                printf($vprintf , $loop_count , $fill_repeat , number_format($destroy_ratio * 100 , 2));
                $last = $v;
                if ($v->is_deleted === 0) {
                    // 未删除 未使用
                    $created_at = strtotime($v->created_at);
                    // 未被删除
                    if ($time < $created_at + $this->duration) {
                        continue ;
                    }
                }
                $total_count++;
                if ($v->disk === 'local') {
                    if (File::exists($v->path)) {
                        File::delete($v->path);
                    }
                } else if ($v->disk === 'aliyun') {
                    $cloud_delete_res = AliyunOss::delete($v->aliyun_bucket , $v->url);
                    if ($cloud_delete_res['code'] > 0) {
                        $failed_count++;
                        continue ;
                    }
                } else {
                    // todo 预留
                }
                ResourceModel::updateById($v->id , [
                    'is_deleted'    => 1 ,
                    'status'        => 1 ,
                    'updated_at' => $datetime ,
                ]);
                $deleted_count++;
            }
            usleep($this->interval);
            $res = ResourceModel::getWaitDeleteByLimitIdAndSize($last->id , $this->limit);
            echo "\n";
        }
        $end_time = date('Y-m-d H:i:s');
        $duration = strtotime($end_time) - strtotime($start_time);
        $end_log = "【end: {$end_time}】，删除完毕。耗费时间：{$duration}s；总待删除数：{$total_count}；实际删除：{$deleted_count}；失败数：{$failed_count}";
        $timer_task_log .= $end_log;
        echo PHP_EOL . $end_log . PHP_EOL;
        TimerTaskLogModel::log($this->signature , $timer_task_log , date('Y-m-d H:i:s'));
    }
}
