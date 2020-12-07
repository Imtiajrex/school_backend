<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassHasSubjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('class_has_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("class_id");
            $table->unsignedBigInteger("department_id");
            $table->unsignedBigInteger("subject_id");


            $table->foreign("class_id")->references("id")->on("class")->onDelete("cascade");
            $table->foreign("department_id")->references("id")->on("department")->onDelete("cascade");
            $table->foreign("subject_id")->references("id")->on("subjects")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('class_departments_has_subjects');
    }
}
