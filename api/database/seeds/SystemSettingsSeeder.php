<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $datetime = date('Y-m-d H:i:s');
        DB::table('xq_system_settings')->updateOrInsert([
            'id' => 1
        ] , [
            'disk' => 'local' ,
            'is_enable_grapha_verify_code_for_login' => 1 ,
            'cloud_storage_manufacturer' => 'aliyun' ,
            'created_at' => $datetime ,
        ]);
    }
}
