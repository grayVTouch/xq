<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSystemSettingsTable extends Migration
{
    private $table = 'xq_system_settings';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table , function (Blueprint $table) {
            $table->id();

            $table->string('web_url' , 500)->default('')->comment('web 端 url 地址');
            $table->tinyInteger('is_enable_grapha_verify_code_for_login')->default(1)->comment('后台登录验证：图形验证码');
            $table->string('aliyun_key' , 255)->default('')->comment('阿里云 key');
            $table->string('aliyun_secret' , 255)->default('')->comment('阿里云 secret');
            $table->string('aliyun_endpoint' , 255)->default('')->comment('阿里云 endpoint');
            $table->string('disk' , 255)->default('local')->comment('存储磁盘：local-本地磁盘 cloud-云存储');

            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
        DB::statement("alter table {$this->table} comment '系统设置表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->table);
    }
}
