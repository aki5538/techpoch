<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStampCorrectionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stamp_correction_requests', function (Blueprint $table) {
            $table->id();
            // 申請者
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            // 対象勤怠
            $table->foreignId('attendance_id')
                ->constrained('attendances')
                ->onDelete('cascade');
            // 申請理由
            $table->string('reason');
            // 承認状態（pending / approved）
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
        Schema::dropIfExists('stamp_correction_requests');
    }
}
