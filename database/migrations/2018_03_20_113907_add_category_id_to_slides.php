<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryIdToSlides extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->integer('category_id')->after('slide_type')->unsigned()->nullable();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
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
        	$table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
}
