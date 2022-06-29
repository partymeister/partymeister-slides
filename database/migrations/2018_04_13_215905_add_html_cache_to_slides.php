<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHtmlCacheToSlides extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->mediumText('cached_html_preview')->after('definitions');
            $table->mediumText('cached_html_final')->after('definitions');
        });
        Schema::table('slide_templates', function (Blueprint $table) {
            $table->mediumText('cached_html_preview')->after('definitions');
            $table->mediumText('cached_html_final')->after('definitions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->dropColumn('cached_html_preview');
            $table->dropColumn('cached_html_final');
        });

        Schema::table('slide_templates', function (Blueprint $table) {
            $table->dropColumn('cached_html_preview');
            $table->dropColumn('cached_html_final');
        });
    }
}
