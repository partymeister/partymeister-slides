<?php

use Culpa\Database\Schema\Blueprint;
use Culpa\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateComponentPlaylistViewersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('component_playlist_viewers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('playlist_id')->unsigned()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('component_playlist_viewers');
    }
}
