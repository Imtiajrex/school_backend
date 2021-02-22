<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_request', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("student_id");
            
            $table->string("payment_category");
            $table->string("payment_info");
            $table->float("payment_amount");
            $table->date("date");
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->nullable();

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
        Schema::dropIfExists('payment_request');
    }
}
