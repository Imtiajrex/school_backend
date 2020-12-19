<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsSold extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_sold', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->integer("quantity");
            $table->float("price");
            $table->unsignedBigInteger('payment_id');
            $table->string("buyer_type");
            $table->unsignedBigInteger('buyer_id');
            $table->date('date');

            $table->foreign("product_id")->references("id")->on("products")->onDelete('cascade');
            $table->foreign("payment_id")->references("id")->on("payment")->onDelete('cascade');
            $table->index(["buyer_type", "buyer_id", "date"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products_sold');
    }
}
