<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSlideClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slide_clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('ip_address');
            $table->string('port');
            $table->integer('sort_position');
            $table->integer('playlist_id')->unsigned()->index()->nullable();
            $table->integer('playlist_item_id')->unsigned()->index()->nullable();
            $table->timestamps();

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->foreign('playlist_id')->references('id')->on('playlists')->onDelete('set null');
            $table->foreign('playlist_item_id')->references('id')->on('playlist_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slide_clients');
    }
}
