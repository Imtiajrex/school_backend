<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_posts', function (Blueprint $table) {
            $table->id();
            $table->string("employee_post");
            $table->unsignedBigInteger("employee_type_id");

            $table->index("employee_type_id")->references("id")->on("employee_types")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_posts');
    }
}
