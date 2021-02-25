<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_messages', function (Blueprint $table) {
            $table->id();
            $table->string("title");
            $table->string("content");
            $table->unsignedBigInteger("student_id");

            $table->foreign("student_id")->references("id")->on("class_has_students")->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_messages');
    }
}
