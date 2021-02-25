<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string("balance_form");
            $table->string("entry_type");
            $table->date("date");
            $table->string("entry_category");
            $table->string("entry_info")->nullable();
            $table->integer("payment_id")->nullable();
            $table->float("amount");

            $table->index("balance_form");
            $table->index("payment_id");
            $table->index("entry_type");
            $table->index("date");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
