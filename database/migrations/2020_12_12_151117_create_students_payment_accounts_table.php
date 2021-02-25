<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsPaymentAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students_payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("student_id");
            $table->unsignedBigInteger("payment_id");
            $table->float("amount");
            $table->enum("status", ["DUE", "PAID"]);

            $table->foreign("student_id")->references("id")->on("class_has_students")->onDelete('cascade');
            $table->foreign("payment_id")->references("id")->on("students_payment")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students_payment_accounts');
    }
}
