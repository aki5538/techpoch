<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceCorrectRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('attendance_correct_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('attendance_id')
                ->constrained('attendances')
                ->onDelete('cascade');

            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->time('break_in')->nullable();
            $table->time('break_out')->nullable();

            $table->string('reason');

            $table->string('status')->default('pending');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_correct_requests');
    }
}
