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
            $table->string("student_id");
            $table->string("student_name");
            $table->string("student_image")->default('');
            $table->string("gender");
            $table->string("religion");
            $table->string("age");
            $table->string("primary_phone");
            $table->string("secondary_phone")->default('');
            $table->string("student_email")->default('');
            $table->string("enrollment_status");
            $table->json("extended_info")->default('{}');

            $table->unique("student_id");
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
