<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee', function (Blueprint $table) {
            $table->id();
            $table->string("employee_id");
            $table->string("employee_name");
            $table->string("mother_name");
            $table->string("father_name");
            $table->string("employee_image")->nullable();
            $table->string("employee_type");
            $table->string("employee_post");
            $table->string("employee_gender");
            $table->string("employee_religion");
            $table->date("date_of_birth");
            $table->string("employee_primary_phone", 25);
            $table->string("employee_secondary_phone", 25)->nullable();
            $table->string("employee_email")->nullable();
            $table->string("job_status");
            $table->json("employee_extended_info")->nullable();

            $table->unique("employee_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee');
    }
}
