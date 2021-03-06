<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string("student_name");
            $table->string("mother_name")->nullable();
            $table->string("father_name")->nullable();
            $table->string("student_image")->nullable();
            $table->string("gender")->nullable();
            $table->string("religion")->nullable();
            $table->date("date_of_birth")->nullable();
            $table->string("primary_phone")->nullable();
            $table->string("secondary_phone")->nullable();
            $table->string("student_email")->nullable();
            $table->string("enrollment_status");
            $table->json("extended_info")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
}
