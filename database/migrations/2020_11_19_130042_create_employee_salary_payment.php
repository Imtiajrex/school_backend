<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeSalaryPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_salary_payment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("employee_id");
            $table->float("employee_salary_amount");
            $table->string("payment_info");
            $table->float("paid_amount");
            $table->string("payment_status");
            $table->date("date");
            $table->time("time");


            $table->foreign("employee_id")->references("id")->on("employee")->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_salary_payment');
    }
}
