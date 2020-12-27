<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGallery extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gallery', function (Blueprint $table) {
            $table->id();
            $table->string("image_name");
            $table->string("caption");
            $table->unsignedBigInteger('parent_album_id')->nullable();


            $table->foreign("parent_album_id")->references("id")->on("album")->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gallery');
    }
}
