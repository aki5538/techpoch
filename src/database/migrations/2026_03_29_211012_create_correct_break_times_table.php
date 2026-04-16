<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectBreakTimesTable extends Migration
{
    public function up()
    {
        Schema::create('correct_break_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_correct_request_id')
              ->constrained()
              ->onDelete('cascade');
            $table->time('break_in')->nullable();
            $table->time('break_out')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('correct_break_times');
    }
}
