<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsPaymentReceipt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students_payment_receipt', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("student_id");
            $table->json("payment_ids");
            $table->date("date");
            $table->time("time");

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
        Schema::dropIfExists('students_payment_receipt');
    }
}
