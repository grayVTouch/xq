<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
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
                'id' => 1 ,
                'name' => '开发模式' ,
                'description' => '' ,
                'is_enabled' => 1 ,
                'is_auth' => 1 ,
                'is_default' => 1 ,
                'weight' => 0 ,
                'created_at' => $datetime ,
            ] ,
            [
                'id' => 2 ,
                'name' => '用户模式' ,
                'description' => '描述' ,
                'is_enabled' => 1 ,
                'is_auth' => 1 ,
                'is_default' => 0 ,
                'weight' => 0 ,
                'created_at' => $datetime ,
            ] ,
        ];
        try {
            DB::beginTransaction();
            foreach ($data as $v)
            {
                $where = [
                    'name' => $v['name'] ,
                ];
                $insertData = $v;
                unset($insertData['name']);
                DB::table('xq_module')->updateOrInsert($where , $insertData);
            }
            DB::commit();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
