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
            $table->string("employee_image")->default('');
            $table->string("employee_type");
            $table->string("employee_post");
            $table->string("employee_gender");
            $table->string("employee_religion");
            $table->integer("employee_age");
            $table->string("employee_primary_phone", 25);
            $table->string("employee_secondary_phone", 25)->default('');
            $table->string("employee_email")->default('');
            $table->string("job_status");
            $table->json("employee_extended_info")->default('{}');

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
