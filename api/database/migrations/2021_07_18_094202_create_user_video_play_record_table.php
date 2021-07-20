<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUserVideoPlayRecordTable extends Migration
{
    private $table = 'user_video_play_record';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table , function (Blueprint $table) {
            $table->id();


            $table->unsignedBigInteger('module_id')->default(0)->comment('module.id');
            $table->unsignedBigInteger('user_id')->default(0)->comment('user.id');
            $table->unsignedBigInteger('video_id')->default(0)->comment('video.id');
            $table->unsignedInteger('played_duration')->default(0)->comment('已播放时长');
            $table->unsignedDecimal('ratio' , 13 , 2)->default(0)->comment('播放进度');
            $table->string('definition' , 255)->default('')->comment('清晰度');
            $table->string('subtitle' , 255)->default('')->comment('字幕');
            $table->unsignedDecimal('volume')->default(0)->comment('音量');

            $table->date('date')->nullable(true)->comment('日期');
            $table->time('time')->nullable(true)->comment('时间');
            $table->dateTime('datetime')->nullable(true)->comment('日期时间');

            $table->timestamps();

            $table->unique(['module_id' , 'user_id' , 'video_id'] , 'm_u_v_unique');

            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
        DB::statement("alter table {$this->table} comment '视频播放记录'");
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
