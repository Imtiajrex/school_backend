<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIssuedBooks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issued_books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->string("book_issuer_type");
            $table->unsignedBigInteger("book_issued_to_id");
            $table->date("book_issued_date");
            $table->date("book_return_date");
            $table->date("returned_at")->nullable();
            $table->string("issue_status");


            $table->foreign("book_id")->references("id")->on("books")->onDelete('cascade');
            $table->index("book_issued_to_id");
            $table->index("issue_status");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('issued_books');
    }
}
