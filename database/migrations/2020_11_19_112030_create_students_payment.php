<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students_payment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("student_id");
            $table->integer("group_id");
            $table->string("payment_category");
            $table->string("payment_info");
            $table->float("payment_amount");
            $table->float("paid_amount");
            $table->date("date");
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->nullable();

            $table->foreign("student_id")->references("id")->on("students")->onDelete('cascade');

            $table->index(["group_id", "date", "created_at", "updated_at", "payment_category_id"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students_payment');
    }
}
