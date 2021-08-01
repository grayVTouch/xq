<?php

namespace App\Console\Commands;

use App\Customize\api\admin\model\AdminPermissionModel;

class AdminPermissionSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:reseed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重新填充权限';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $artisan = base_path('artisan');
        // 第一步：清空表
        AdminPermissionModel::truncate();
        // 第二部：填充数据
        exec(sprintf("php %s db:seed --class=AdminPermissionSeeder" , $artisan));
    }
}
