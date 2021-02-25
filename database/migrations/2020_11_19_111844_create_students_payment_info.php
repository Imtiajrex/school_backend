<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsPaymentInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students_payment_info', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("student_id");
            $table->string("student_payment_category");
            $table->string("student_default_fees");

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
        Schema::dropIfExists('students_payment_info');
    }
}
