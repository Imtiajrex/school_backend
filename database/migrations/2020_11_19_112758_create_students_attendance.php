<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsAttendance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("student_id");
            $table->time("access_time")->nullable();
            $table->date("date");
            $table->boolean("manual")->default(false);


            $table->foreign("student_id")->references("id")->on("students")->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students_attendance');
    }
}
