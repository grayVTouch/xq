<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datetime = date('Y-m-d H:i:s');
        $data = [
            [
                'name' => '首页幻灯片' ,
                'value' => 'home' ,
                'platform' => 'web' ,
                'description' => '' ,
                'created_at' => $datetime ,
            ] ,
            [
                'name' => '图片专题' ,
                'value' => 'image_project' ,
                'platform' => 'web' ,
                'description' => '' ,
                'created_at' => $datetime ,
            ] ,
            [
                'name' => '视频专题' ,
                'value' => 'video_project' ,
                'platform' => 'web' ,
                'description' => '' ,
                'created_at' => $datetime ,
            ] ,
            [
                'name' => '图片' ,
                'value' => 'image' ,
                'platform' => 'web' ,
                'description' => '非专题图片' ,
                'created_at' => $datetime ,
            ] ,
            [
                'name' => '视频' ,
                'value' => 'video' ,
                'platform' => 'web' ,
                'description' => '非专题视频' ,
                'created_at' => $datetime ,
            ] ,
        ];

        try {
            DB::beginTransaction();
            foreach ($data as $v)
            {
                $where = [
                    'name' => $v['name'] ,
                    'value' => $v['value'] ,
                    'platform' => $v['platform'] ,
                ];
                $insertData = $v;
                unset($insertData['name']);
                unset($insertData['value']);
                unset($insertData['platform']);
                DB::table('xq_position')->updateOrInsert($where , $insertData);
            }
            DB::commit();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
