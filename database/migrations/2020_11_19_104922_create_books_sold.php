<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksSold extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books_sold', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->integer("quantity");
            $table->float("price");
            $table->string("buyer_type");
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('payment_id');
            $table->date('date');


            $table->foreign("book_id")->references("id")->on("books")->onDelete('cascade');

            $table->index('payment_id');
            $table->index('buyer_id');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books_sold');
    }
}
