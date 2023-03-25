<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlideIdToPlaylistItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('playlist_items', function (Blueprint $table) {
            $table->bigInteger('slide_id')->after('slide_type')->unsigned()->nullable();

            $table->foreign('slide_id')->references('id')->on('slides')->onDelete('set null');
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
            $table->dropForeign(['slide_id']);
            $table->dropColumn('slide_id');
        });
    }
}
