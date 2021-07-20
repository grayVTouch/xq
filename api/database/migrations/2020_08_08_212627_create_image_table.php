<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateImageTable extends Migration
{
    public $table = 'xq_image';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table , function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('module_id')->default(0)->comment('xq_module.id，缓存字段');
            $table->unsignedBigInteger('user_id')->default(0)->comment('xq_user.id，缓存字段');
            $table->unsignedBigInteger('category_id')->default(0)->comment('xq_category.id，缓存字段');
            $table->unsignedBigInteger('image_project_id')->default(0)->comment('xq_image_project.id');
            $table->string('src' , 500)->default('')->comment('图片源 - 压缩图');
            $table->string('original_src' , 500)->default('')->comment('图片源 - 原图');
            $table->unsignedBigInteger('view_count')->default(0)->comment('浏览次数');
            $table->unsignedBigInteger('praise_count')->default(0)->comment('获赞次数');
            $table->unsignedBigInteger('collect_count')->default(0)->comment('收藏次数');

            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
        DB::statement("alter table {$this->table} comment '图片专题包含的图片'");
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
