<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourceTable extends Migration
{
    private $table = 'xq_resource';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table , function (Blueprint $table) {
            $table->id();
            $table->string('url' , 1000)->default('')->comment('资源绝对网络路径');
            $table->string('path' , 1000)->default('')->comment('资源绝对本地磁盘路径');
            $table->string('disk')->default('')->comment('存储介质: local-本地存储 aliyun-阿里云 qiniu-七牛云等');
            $table->string('cloud_bucket' , 255)->default('')->comment('云存储-目录（桶）');
            $table->tinyInteger('is_used')->default(0)->comment('已使用？0-否 1-是');
            $table->tinyInteger('is_deleted')->default(0)->comment('已删除？0-否 1-是');
            $table->tinyInteger('status')->default(0)->comment('状态：0-未执行 1-已执行');

            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
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
