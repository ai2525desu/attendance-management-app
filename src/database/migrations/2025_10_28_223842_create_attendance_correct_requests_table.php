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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->date('request_date');
            $table->datetime('correct_clock_in')->nullable();
            $table->datetime('correct_clock_out')->nullable();
            $table->text('remarks');
            $table->enum('status', ['pending', 'approved']);
            $table->boolean('edited_by_admin')->default(false)->comment('true:管理者直接修正, false:一般ユーザー申請');
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
