<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResultHasExam extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('result_has_exam', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("result_id");
            $table->unsignedBigInteger("exam_id");
            $table->float("exam_percentage");

            $table->foreign("result_id")->references('id')->on("results");
            $table->foreign("exam_id")->references('id')->on("exam");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('result_has_exam');
    }
}
