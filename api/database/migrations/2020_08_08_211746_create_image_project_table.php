<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateImageProjectTable extends Migration
{
    public $table = 'xq_image_project';

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
            $table->unsignedBigInteger('user_id')->default(0)->comment('xq_user.id');
            $table->unsignedBigInteger('module_id')->default(0)->comment('xq_module.id');
            $table->unsignedBigInteger('category_id')->default(0)->comment('xq_category.id');
            $table->string('type' , 100)->default('')->comment('类别：pro-专题 misc-杂类');
            $table->unsignedBigInteger('image_subject_id')->default(0)->comment('xq_subject.id');
            $table->string('thumb' , 500)->default('')->comment('封面');
            $table->string('description' , 500)->default('')->comment('描述');
            $table->integer('weight')->default(0)->comment('权重');
            $table->unsignedBigInteger('view_count')->default(0)->comment('浏览次数');
            $table->unsignedBigInteger('praise_count')->default(0)->comment('获赞次数');
            $table->unsignedBigInteger('collect_count')->default(0)->comment('收藏次数');
            $table->tinyInteger('status')->default(0)->comment('审核状态：-1-审核失败 0-待审核 1-审核通过');
            $table->string('fail_reason' , 1000)->default('')->comment('失败原因，当 status=-1 时，必须提供');
            $table->string('directory' , 1024)->default('')->comment('目录');

            $table->tinyInteger('process_status')->default(0)->comment('处理状态：-1-处理失败 0-待处理 1-处理中 2-处理完毕');
            $table->text('process_message')->nullable(true)->comment('处理结果：信息');
            $table->text('process_data')->nullable(true)->comment('处理结果：数据');
            $table->string('disk' , 255)->default('')->comment('system_settings.disk；存储介质：local-本地存储 aliyun-阿里云 等');

            $table->timestamps();

            $table->index('module_id');
            $table->index('category_id');
            $table->index('image_subject_id');

            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
        DB::statement("alter table {$this->table} comment '图片专题表'");
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
