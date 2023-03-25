<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransitionId2ToPlaylistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('playlist_items', function (Blueprint $table) {
            $table->bigInteger('transition_slidemeister_id')->after('transition_id')->unsigned()->nullable()->index();
            $table->foreign('transition_slidemeister_id')->references('id')->on('transitions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('playlist_items', function (Blueprint $table) {
            $table->dropForeign(['transition_slidemeister_id']);
            $table->dropColumn('transition_slidemeister_id');
        });
    }
}
