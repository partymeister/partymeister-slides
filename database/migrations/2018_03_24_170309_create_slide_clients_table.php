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
            $table->id();
            $table->string('name');
            $table->string('ip_address');
            $table->string('port');
            $table->integer('sort_position');
            $table->bigInteger('playlist_id')->unsigned()->index()->nullable();
            $table->bigInteger('playlist_item_id')->unsigned()->index()->nullable();
            $table->timestamps();

            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();

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
