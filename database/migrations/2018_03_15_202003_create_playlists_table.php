<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlaylistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('playlists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->boolean('is_competition');

            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();

            $table->timestamps();
        });

        Schema::create('playlist_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('playlist_id')->unsigned()->index();
            $table->string('type');
            $table->string('slide_type');
            $table->integer('duration');
            $table->bigInteger('transition_id')->unsigned()->nullable()->index();
            $table->integer('transition_duration');
            $table->boolean('is_advanced_manually');
            $table->boolean('is_muted');
            $table->integer('midi_note');
            $table->json('metadata');
            $table->string('callback_hash');
            $table->integer('callback_delay');

            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();

            $table->timestamps();

            $table->foreign('playlist_id')->references('id')->on('playlists')->onDelete('cascade');
            $table->foreign('transition_id')->references('id')->on('transitions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('playlist_items');
        Schema::dropIfExists('playlists');
    }
}
