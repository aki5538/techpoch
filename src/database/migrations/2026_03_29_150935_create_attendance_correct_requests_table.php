<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceCorrectRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_correct_requests', function (Blueprint $table) {
            $table->id();
            // 申請者
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // 対象勤怠
            $table->foreignId('attendance_id')
                ->constrained('attendances')
                ->onDelete('cascade');

            // 修正後の打刻内容
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->time('break_in')->nullable();
            $table->time('break_out')->nullable();

            // 申請理由
            $table->string('reason');

            // 承認状態
            $table->string('status')->default('pending');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_correct_requests');
    }
}
