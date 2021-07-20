<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePraiseTable extends Migration
{
    private $table = 'xq_praise';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table , function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->default(0)->comment('xq_user.id');
            $table->string('relation_type' , 255)->default('')->comment('关联类型: 比如 image_subject-图片专题');
            $table->unsignedBigInteger('relation_id')->default(0)->comment('关联表id');
            $table->unsignedBigInteger('module_id')->default(0)->comment('xq_module.id');
            $table->date('date')->nullable(true)->comment('日期');
            $table->time('time')->nullable(true)->comment('时间');

            $table->timestamps();

            $table->unique(['user_id' , 'relation_type' , 'relation_id' , 'module_id'] , 'unique');

            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
        DB::statement("alter table {$this->table} comment '点赞表'");
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
