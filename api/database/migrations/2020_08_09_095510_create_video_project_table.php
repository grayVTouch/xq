<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateVideoProjectTable extends Migration
{
    private $table = 'xq_video_project';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table , function (Blueprint $table) {
            $table->id();
            $table->string('name' , 255)->default('')->comment('名称');
            $table->string('thumb' , 500)->default('')->comment('封面');
            $table->decimal('score' , 13 , 2)->default(0)->comment('评分');
            $table->year('release_year')->nullable(true)->comment('发布年份');
            $table->date('release_date')->nullable(true)->comment('发布日期');
            $table->date('end_date')->nullable(true)->comment('完结日期');
            $table->string('end_status' , 30)->default('completed')->comment('完结状态：making-制作中 completed-已完结 terminated-已终止（部分完成）');
            $table->unsignedSmallInteger('count')->default(0)->comment('视频数量');
            $table->unsignedSmallInteger('min_index')->default(1)->comment('视频剧集的开始');
            $table->unsignedSmallInteger('max_index')->default(1)->comment('视频剧集的结束');
            $table->unsignedSmallInteger('index_split_count')->default(30)->comment('剧集切割的数量');

            $table->string('description' , 1000)->default('')->comment('描述');
            $table->unsignedBigInteger('video_series_id')->default(0)->comment('xq_video_series.id');
            $table->unsignedBigInteger('category_id')->default(0)->comment('xq_category.id');
            $table->unsignedBigInteger('video_company_id')->default(0)->comment('xq_video_company.id');
            $table->unsignedBigInteger('module_id')->default(0)->comment('xq_module.id');
            $table->integer('weight')->default(0)->comment('权重');

            $table->unsignedBigInteger('user_id')->default(0)->comment('xq_user.id，发布者用户id');
            $table->tinyInteger('status')->default(0)->comment('状态： -1-审核不通过 0-待审核 1-审核通过');
            $table->string('fail_reason' , 255)->default('')->comment('失败原因，当status=-1时，必须提供');
            $table->tinyInteger('file_process_status')->default(0)->comment('文件处理处理状态：-1-处理失败 0-待处理 1-处理中 2-处理完成 ');
            $table->text('file_process_message')->nullable(true)->comment('文件处理：信息');
            $table->text('file_process_data')->nullable(true)->comment('文件处理：数据');

            $table->string('disk' , 255)->default('')->comment('system_settings.disk；存储介质：local-本地存储 aliyun-阿里云 等');

            $table->unsignedBigInteger('praise_count')->default(0)->comment('点赞数');
            $table->unsignedBigInteger('against_count')->default(0)->comment('反对数');
            $table->unsignedBigInteger('view_count')->default(0)->comment('观看次数');
            $table->unsignedBigInteger('play_count')->default(0)->comment('播放数量');
            $table->unsignedBigInteger('collect_count')->default(0)->comment('收藏量');
            $table->string('directory' , 1024)->default('')->comment('目录');



            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
        DB::statement("alter table {$this->table} comment '视频专题'");
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
