<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("student_id");
            $table->unsignedBigInteger("exam_id");
            $table->unsignedBigInteger("subject_id");
            $table->json("marks");


            $table->foreign("student_id")->references("id")->on("students")->onDelete('cascade');
            $table->foreign("exam_id")->references("id")->on("exam")->onDelete('cascade');
            $table->foreign("subject_id")->references("id")->on("subjects")->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marks');
    }
}
