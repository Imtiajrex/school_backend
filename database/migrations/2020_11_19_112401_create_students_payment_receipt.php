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
            $table->unsignedBigInteger("id");
            $table->unsignedBigInteger("student_id");
            $table->unsignedBigInteger("payment_id");
            $table->date("date");
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->nullable();

            $table->index("id");
            $table->foreign("student_id")->references("id")->on("class_has_students")->onDelete('cascade');
            $table->foreign("payment_id")->references("id")->on("students_payment")->onDelete('cascade');
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
